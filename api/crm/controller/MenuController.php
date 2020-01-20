<?php


namespace api\crm\controller;


use api\crm\model\UserModel;
use api\crm\model\UserRoleModel;
use cmf\controller\RestBaseController;

class MenuController extends RestBaseController
{
    public function getUserMenu()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::with(['relation' => ['role_info']])
            ->field('id,true_name,user_login,mobile,user_type,last_login_time,openid')
            ->get($user_id);
        if ($user_info['user_type'] == 1) {
            $this->error('后台管理员不能登陆');
        }
        $user_info['last_login_time'] = date('Y-m-d H:i:s', $user_info['last_login_time']);
        $user_role = UserRoleModel::get($user_info['user_role_id']);
        $menus = $user_role->auths->where('parent_id', 0)->each(function ($item) {
            if ($item['icon']) {
                $item['icon'] = $this->request->domain() . $item['icon'];
            }
            return $item;
        });
        $this->success('获取菜单', ['user_info' => $user_info, 'menus' => $menus]);
    }
}
