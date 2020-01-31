<?php

namespace plugins\wechat\controller;

use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\PluginAdminBaseController;
use EasyWeChat\Factory;
use plugins\wechat\model\CacheModel;
use plugins\wechat\model\TemplateMessageModel;
use plugins\wechat\model\WechatMenusModel;
use plugins\wechat\model\WechatUserModel;
use think\Db;

/**
 * Class AdminIndexController.
 *
 * @adminMenuRoot(
 *     'name'   =>'微信插件',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 0,
 *     'icon'   =>'dashboard',
 *     'remark' =>'微信插件'
 * )
 */
class AdminIndexController extends WechatBaseController
{
    protected function initialize()
    {
        parent::initialize();
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        }

    }

    /**
     * 微信菜单设置
     * @adminMenu(
     *     'name'   => '微信菜单设置',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '微信菜单设置',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $users = Db::name("user")->limit(0, 5)->select();
        $this->assign("users", $users);
        return $this->fetch('/admin_index');
    }

    public function create_menus()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $options = $this->options;
            $app = Factory::officialAccount($options);
            $model = WechatMenusModel::where('company_id', $this->company_id)->find();
            if ($data['menu']) {
                $app->menu->create(json_decode($data['menu'])->button);
            }
            $model->menu = $data['menu'];
            $model->save();
            $this->success('ok', '', json_decode($data['menu'])->button);
        }
    }

    public function user_list()
    {
        $options = $this->options;
        $app = Factory::officialAccount($options);
        $users = $app->user->list();
//        $user = $app->user->get('ogSgl0mz01jRNtg455pjNit8Lp_c');
//        $user_model = WechatUserModel::create($user);
//        $user_model->save();
//        var_dump($users);
        return $this->fetch('/user_list');
    }

    public function template_message()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            $where = [];
            if ($admin_info['company_id']) {
                $where[] = ['company_id', '=', $admin_info['company_id']];
            }
            $list = TemplateMessageModel::with('company_info')
                ->where($where)->order('create_time desc')
                ->paginate(10, false, ['page' => $page]);
            $this->success('', '', $list);
        }
        return $this->fetch('/template_message');
    }

    public function template_save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $model = TemplateMessageModel::get($data['id']);
            } else {
                $model = new TemplateMessageModel();
            }
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $model->company_id = $admin_info['company_id'];
            } else {
                $model->company_id = $data['company_id'];
            }
            $model->template_id = $data['template_id'];
            $model->template_title = $data['template_title'];
            $model->template_content = $data['template_content'];
            $model->status = $data['status'];

            $res = $model->save();
            if ($res) {
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
    }

    public function change_status()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = TemplateMessageModel::get($data['id']);
            if ($model) {
                $res = TemplateMessageModel::where(['id' => $data['id']])->update([$data['field'] => $data['value']]);
                if ($res) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error('模板信息不存在或已删除');
            }
        }
    }

}
