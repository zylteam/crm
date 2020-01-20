<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use api\crm\model\CustomerStatusModel;
use api\crm\model\UserModel;
use app\admin\model\CompanyModel;
use cmf\controller\AdminBaseController;
use Overtrue\Socialite\User;
use think\Db;
use app\admin\model\Menu;

class MainController extends AdminBaseController
{

    /**
     *  后台欢迎页
     */
    public function index()
    {
        $dashboardWidgets = [];
        $widgets = cmf_get_option('admin_dashboard_widgets');

        $defaultDashboardWidgets = [
            '_SystemCmfHub' => ['name' => 'CmfHub', 'is_system' => 1],
            '_SystemCmfDocuments' => ['name' => 'CmfDocuments', 'is_system' => 1],
            '_SystemMainContributors' => ['name' => 'MainContributors', 'is_system' => 1],
            '_SystemContributors' => ['name' => 'Contributors', 'is_system' => 1],
            '_SystemCustom1' => ['name' => 'Custom1', 'is_system' => 1],
            '_SystemCustom2' => ['name' => 'Custom2', 'is_system' => 1],
            '_SystemCustom3' => ['name' => 'Custom3', 'is_system' => 1],
            '_SystemCustom4' => ['name' => 'Custom4', 'is_system' => 1],
            '_SystemCustom5' => ['name' => 'Custom5', 'is_system' => 1],
        ];

        if (empty($widgets)) {
            $dashboardWidgets = $defaultDashboardWidgets;
        } else {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    $dashboardWidgets['_System' . $widget['name']] = ['name' => $widget['name'], 'is_system' => 1];
                } else {
                    $dashboardWidgets[$widget['name']] = ['name' => $widget['name'], 'is_system' => 0];
                }
            }

            foreach ($defaultDashboardWidgets as $widgetName => $widget) {
                $dashboardWidgets[$widgetName] = $widget;
            }


        }

        $dashboardWidgetPlugins = [];

        $hookResults = hook('admin_dashboard');

        if (!empty($hookResults)) {
            foreach ($hookResults as $hookResult) {
                if (isset($hookResult['width']) && isset($hookResult['view']) && isset($hookResult['plugin'])) { //验证插件返回合法性
                    $dashboardWidgetPlugins[$hookResult['plugin']] = $hookResult;
                    if (!isset($dashboardWidgets[$hookResult['plugin']])) {
                        $dashboardWidgets[$hookResult['plugin']] = ['name' => $hookResult['plugin'], 'is_system' => 0];
                    }
                }
            }
        }

        $smtpSetting = cmf_get_option('smtp_setting');

        $this->assign('dashboard_widgets', $dashboardWidgets);
        $this->assign('dashboard_widget_plugins', $dashboardWidgetPlugins);
        $this->assign('has_smtp_setting', empty($smtpSetting) ? false : true);

        $admin_id = cmf_get_current_admin_id();
        $admin_info = UserModel::get($admin_id);
        $where = [];
        if ($admin_info['company_id']) {
            $where['company_id'] = $admin_info['company_id'];
        }
        $companyData = CompanyModel::all();
        $this->assign('companyData', $companyData);
        $all_user_count = UserModel::where(['user_type' => 2, 'role_name' => '', 'is_delete' => 0])
            ->where($where)->count();
        $today_user_count = UserModel::where(['user_type' => 2, 'role_name' => '', 'is_delete' => 0])
            ->where($where)->whereTime('create_time', 'today')->count();
        $this->assign('session_admin_id', $admin_id);
        $this->assign('all_user_count', $all_user_count);
        $this->assign('today_user_count', $today_user_count);
        return $this->fetch();
    }

    public function dashboardWidget()
    {
        $dashboardWidgets = [];
        $widgets = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

    public function customer_statistics()
    {
        $data = $this->request->param();
        $admin_id = cmf_get_current_admin_id();
        $admin_user_info = UserModel::get($admin_id);
        $where = [];
        if ($admin_user_info['company_id']) {
            $where[] = ['company_id', '=', $admin_user_info['company_id']];
        }
        $customer_status = CustomerStatusModel::field('id,name')
            ->withCount(['customer_list' => function ($query) {
                $data = $this->request->param();
                $begin_time = isset($data['begin_time']) && $data['begin_time'] ? $data['begin_time'] : '';
                $end_time = isset($data['end_time']) && $data['end_time'] ? $data['end_time'] : '';
                if ($begin_time && $end_time) {
                    $where[] = ['create_time', 'between time', [$begin_time, $end_time]];
                }
                $parent_id_where = [];
                $where[] = ['role_name', '=', ''];
                $where[] = ['status', '=', 0];
                $where[] = ['user_type', '=', 2];
                $where[] = ['is_delete', '=', 0];
                if ($parent_id_where) {
                    $where[] = $parent_id_where;
                }
                $query->where($where);
            }])
            ->where($where)
            ->all();
        $this->success('', '', ['aa' => $customer_status]);
    }

}
