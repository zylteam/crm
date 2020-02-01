<?php


namespace app\admin\controller;


use app\admin\model\ActivitySignModel;
use app\admin\model\ActivityModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class ActivityController extends AdminBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $where = [];
            $num = 10;
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $where[] = ['company_id', '=', $admin_info['company_id']];
            }
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', [$data['duration'][0] . ' 00:00:00', $data['duration'][1] . ' 23:59:59']];
            }
            $where[] = ['is_delete', '=', 0];
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $list = ActivityModel::where($where)
                ->order('create_time desc')
                ->paginate($num, false, ['page' => $page])->each(function ($item) {
                    if ($item['imgs']) {
                        $item['imgs'] = explode(',', $item['imgs']);
                    } else {
                        $item['imgs'] = [];
                    }
                    $item['begin_time'] = date('Y-m-d', $item['begin_time']);
                    $item['end_time'] = date('Y-m-d', $item['end_time']);
                    $item['sign_begin_time'] = date('Y-m-d', $item['sign_begin_time']);
                    $item['sign_end_time'] = date('Y-m-d', $item['sign_end_time']);
                    $item['activity_time'] = [$item['begin_time'], $item['end_time']];
                    $item['sign_time'] = [$item['sign_begin_time'], $item['sign_end_time']];
                });
            $this->success('', '', $list);
        }
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->param()) {
            $data = $this->request->param();
            if (isset($data['activity_time']) && $data['activity_time']) {
                $data['begin_time'] = strtotime($data['activity_time'][0]);
                $data['end_time'] = strtotime($data['activity_time'][1]);
            }
            if (isset($data['sign_time']) && $data['sign_time']) {
                $data['sign_begin_time'] = strtotime($data['sign_time'][0]);
                $data['sign_end_time'] = strtotime($data['sign_time'][1]);
            }
            if (isset($data['imgs']) && $data['imgs']) {
                $data['imgs'] = implode(',', $data['imgs']);
            }
            if (isset($data['company_id']) && $data['company_id']) {

            } else {
                $admin_id = cmf_get_current_admin_id();
                $admin_info = UserModel::get($admin_id);
                $data['company_id'] = $admin_info['company_id'];
            }
            if (isset($data['id']) && $data['id']) {
                $model = ActivityModel::get($data['id']);
                $model->title = $data['title'];
                $model->sub_title = $data['sub_title'];
                $model->address = $data['address'];
                $model->content = $data['content'];
                $model->money = $data['money'];
                $model->give_points = $data['give_points'];
                $model->begin_time = $data['begin_time'];
                $model->end_time = $data['end_time'];
                $model->sign_begin_time = $data['sign_begin_time'];
                $model->sign_end_time = $data['sign_end_time'];
                $model->quota_num = $data['quota_num'];
                $model->company_id = $data['company_id'];
                $model->cover_img = $data['cover_img'];
                $model->imgs = $data['imgs'];
                $model->is_hot = $data['is_hot'];
            } else {
                $model = ActivityModel::create($data);
            }
            $res = $model->save();
            if ($res) {
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
        }
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = ActivityModel::get($data['id']);
            if ($model) {
                $model->is_delete = 1;
                $res = $model->save();
                if ($res) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } else {
                $this->error('活动不存在或已被删除');
            }

        }
    }


    public function change_status()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = ActivityModel::get($data['id']);
            if ($model) {
                $res = ActivityModel::where(['id' => $data['id']])->update([$data['field'] => $data['value']]);
                if ($res) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error('活动不存在或已被删除');
            }
        }
    }

    public function sign_list()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $where = [];
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', [$data['duration'][0] . ' 00:00:00', $data['duration'][1] . ' 23:59:59']];
            }
            $num = 10;
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $list = ActivitySignModel::with('activity_info,user_info')
                ->where($where)
                ->order('create_time desc')
                ->paginate($num, false, ['page' => $page])
                ->each(function ($item) {

                    return $item;
                });
            $this->success('', '', $list);
        }

        return $this->fetch();
    }
}
