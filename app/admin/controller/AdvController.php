<?php


namespace app\admin\controller;


use app\admin\model\AdvModel;
use app\admin\model\AdvPositionModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class AdvController extends AdminBaseController
{
    public function adv_list()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $num = 10;
            $where = [];
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $where['company_id'] = $admin_info['company_id'];
            }
            $list = AdvModel::with('position,company_info')
                ->where($where)
                ->order('sort desc,status desc,create_time desc')
                ->paginate($num, false, ['page' => $page]);
            $adv_address = AdvPositionModel::all();
            $this->success('', null, ['list' => $list->items(), 'count' => $list->total(), 'adv_address' => $adv_address]);
        }
        return $this->fetch();
    }

    public function position()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $num = 10;
            $list = AdvPositionModel::withCount('adv_list')
                ->order('create_time desc')
                ->paginate($num, false, ['page' => $page]);
            $this->success('', null, ['list' => $list->items(), 'count' => $list->total()]);
        }
        return $this->fetch();
    }

    public function category()
    {
        return $this->fetch();
    }

    public function set_adv()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            if ($data['id'] && isset($data['id'])) {
                $position = AdvPositionModel::get($data['id']);
            } else {
                $position = new  AdvPositionModel();
            }
            $position->position_name = $data['position_name'];
//            $position->num = $data['num'];
            $position->status = isset($data['status']) && $data['status'] ? $data['status'] : 0;
            $res = $position->save();
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }

        }
    }

    public function set_advlist()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $adv_model = AdvModel::get($data['id']);
            } else {
                $adv_model = new AdvModel();
            }
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $company_id = $admin_info['company_id'];
            } else {
                $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
            }
            $adv_model->company_id = $company_id;
            $adv_model->position_id = $data['position_id'];
            $adv_model->sort = $data['sort'];
            $adv_model->adv_id = $data['position_id'];
            $adv_model->title = $data['title'];
            $adv_model->adv_img = $data['adv_img'];
            $adv_model->status = $data['status'];
            $adv_model->type = $data['type'];
            $adv_model->link_url = $data['link_url'];
            $res = $adv_model->save();
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }

        }

    }

    public function change_status()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = AdvModel::get($data['id']);
            if ($model) {
                $res = AdvModel::where(['id' => $data['id']])->update([$data['field'] => $data['value']]);
                if ($res) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error('广告不存在或已被删除');
            }
        }
    }

    public function delete_advlist()
    {
        $data = $this->request->param();
        $res = Advlist::destroy($data['id']);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
