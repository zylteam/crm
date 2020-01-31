<?php


namespace app\admin\controller;


use app\admin\model\UserModel;
use app\admin\model\WechatSettingModel;
use cmf\controller\AdminBaseController;

class LiveController extends AdminBaseController
{
    public function setting()
    {
        if ($this->request->isAjax()) {
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $setting = WechatSettingModel::where('company_id', $user_info['company_id'])->find();
            $setting['content'] = unserialize($setting['setting']);
            $this->success('setting', '', $setting);
        }
        return $this->fetch();
    }
}
