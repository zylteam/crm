<?php


namespace app\admin\controller;


use app\admin\model\OtherModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class OtherController extends AdminBaseController
{

    public function setting()
    {
        if ($this->request->isAjax()) {
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);

            $setting = [];
            if ($user_info['company_id']) {
                $setting = OtherModel::where('company_id', $user_info['company_id'])->find();
                $setting['content'] = unserialize($setting['content']);
            } else {
                $setting = OtherModel::where('company_id', 1)->find();
                $setting['content'] = unserialize($setting['content']);
            }
            $this->success('', '', $setting);
        }
        return $this->fetch();
    }

    public function update_setting()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $array['time'] = $data['time'];
            $array['message'] = $data['message'];
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $company_id = 0;
            if ($user_info['company_id']) {
                $company_id = $user_info['company_id'];
            } else {
                $company_id = isset($data['company_id']) ? $data['company_id'] : 0;
            }
            if (isset($data['id']) && $data['id']) {
                $model = OtherModel::get($data['id']);
                $model->content = serialize($array);
            } else {
                $model = new OtherModel([
                    'company_id' => $company_id,
                    'content' => serialize($array)
                ]);
            }

            $res = $model->save();
            if ($res) {
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
    }

}
