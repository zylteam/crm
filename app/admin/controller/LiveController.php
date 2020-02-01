<?php


namespace app\admin\controller;


use app\admin\model\LiveSettingModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use app\admin\model\WechatSettingModel;
use cmf\controller\AdminBaseController;

class LiveController extends AdminBaseController
{
    public function setting()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $setting = LiveSettingModel::where('company_id', $user_info['company_id'])->find();
            $this->success('setting', '', $setting);
        }
        return $this->fetch();
    }

    public function updateSetting()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            if (isset($data['id']) && $data['id']) {
                $setting = LiveSettingModel::get($data['id']);
            } else {
                $setting = new  LiveSettingModel();
            }
            if ($user_info['company_id']) {
                $setting->company_id = $user_info['company_id'];
            } else {
                $setting->company_id = $data['company_id'];
            }
            $setting->AccessKeyID = $data['AccessKeyID'];
            $setting->AccessKeySecret = $data['AccessKeySecret'];
            $setting->pull_key = $data['pull_key'];
            $setting->push_key = $data['push_key'];
            $setting->pull_address = $data['pull_address'];
            $setting->push_address = $data['push_address'];
            $res = $setting->save();
            if ($res) {
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
    }

    public function room_list()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
        }
        return $this->fetch();
    }
}
