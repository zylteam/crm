<?php


namespace app\admin\controller;


use app\admin\model\CustomerSourceSettingModel;
use app\admin\model\CustomerStatusModel;
use app\admin\model\IntentionTagModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class CustomerStatusController extends AdminBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $admin_id = cmf_get_current_admin_id();
            $user_info = UserModel::get($admin_id);
            $where = [];
            if (isset($data['company_id']) && $data['company_id']) {
                $where['company_id'] = $data['company_id'];
            } else {
                if ($user_info['company_id']) {
                    $where['company_id'] = $user_info['company_id'];
                }
            }

            $list = CustomerStatusModel::with(['tag_list', 'company_info'])
                ->where($where)
                ->order('sort asc')
                ->all();
            $this->success('', '', $list);
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
                $model = CustomerStatusModel::get($data['id']);
                $model->name = $data['name'];
                $model->remark = $data['remark'];
                $model->type = $data['type'];
                if ($data['company_id']) {
                    $model->company_id = $data['company_id'];
                }
            } else {
                $model = CustomerStatusModel::create($data);
            }
            $res = $model->save();
            if ($res) {
                $status_id = $model->id;
                $tag = [];
                foreach ($data['tag_list'] as $key => $value) {
                    if ($value['name']) {
                        if (isset($value['id']) && $value['id']) {
                            $tag[$key]['id'] = $value['id'];
                        }
                        $tag[$key]['status_id'] = $status_id;
                        $tag[$key]['name'] = $value['name'];
                    }
                }
                $tag_model = new IntentionTagModel();
                $result = $tag_model->saveAll($tag);
                $this->success('ok');
            } else {
                $this->error('fail');
            }
        }
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = CustomerStatusModel::get($data['id'], 'tag_list');
            if ($model) {
                $res = $model->together('tag_list')->delete();
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

    public function delete_tag()
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

    public function change_status()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $setting = CustomerStatusModel::get($data['id']);
            if ($setting) {
                $res = CustomerStatusModel::where('id', $data['id'])->update([$data['field'] => $data['value']]);
                if ($res) {
                    $this->success('ok');
                } else {
                    $this->error('error');
                }
            } else {
                $this->error('数据异常');
            }
        }
    }
}
