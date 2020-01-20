<?php

namespace plugins\wechat\controller;

use cmf\controller\PluginAdminBaseController;
use EasyWeChat\Factory;
use plugins\wechat\model\CacheModel;
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

}
