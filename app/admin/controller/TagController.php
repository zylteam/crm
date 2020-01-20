<?php


namespace app\admin\controller;


use app\admin\model\CustomerSourceSettingModel;
use app\admin\model\IntentionTagModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class TagController extends AdminBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $where = array('type' => 1);
            if ($user_info['company_id']) {
                $where['company_id'] = $user_info['company_id'];
            }
            $setting = IntentionTagModel::with('company_info')
                ->where($where)->order('create_time desc')->all();
            $this->success('', '', $setting);
        }
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            if ($user_info['company_id']) {
                $data['company_id'] = $user_info['company_id'];
            }

            if (isset($data['id']) && $data['id']) {
                $model = IntentionTagModel::get($data['id']);
                $model->name = $data['name'];
                if ($data['company_id']) {
                    $model->company_id = $data['company_id'];
                }
            } else {
                $data['type'] = 1;
                $model = IntentionTagModel::create($data);
            }
            $res = $model->save();
            if ($res) {
                $this->success('ok');
            } else {
                $this->error('error');
            }
        }
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = IntentionTagModel::get($data['id']);
            if ($model) {
                $res = $model->delete();
                if ($res) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } else {
                $this->error('数据异常');
            }
        }
    }
}
