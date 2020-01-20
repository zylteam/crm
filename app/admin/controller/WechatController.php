<?php


namespace app\admin\controller;


use app\admin\model\UserModel;
use app\admin\model\WechatSettingModel;
use cmf\controller\AdminBaseController;

class WechatController extends AdminBaseController
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

    public function update_setting()
    {
        if ($this->request->post()) {
            $data = $this->request->param();
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $setting = [];
            if (isset($data['company_id']) && $data['company_id']) {
                $setting['company_id'] = $data['company_id'];
            } else {
                $setting['company_id'] = $user_info['company_id'];
            }
            if (isset($data['id']) && $data['id']) {
                $model = WechatSettingModel::get($data['id']);
                $model->setting = serialize($data);
            } else {
                $setting['setting'] = serialize($data);
                $model = WechatSettingModel::create($setting);
            }

            $res = $model->save();
            if ($res) {
                $this->success('更新成功');
            } else {
                $this->$this->error('更新失败');
            }
        }
    }
}
