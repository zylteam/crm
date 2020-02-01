<?php


namespace app\admin\controller;


use app\admin\model\RoleUserModel;
use cmf\controller\AdminBaseController;

class CouponController extends AdminBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {

        }
        return $this->fetch();
    }
}
