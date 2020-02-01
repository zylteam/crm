<?php


namespace app\admin\controller;


use app\admin\model\GoodsModel;
use app\admin\model\GoodsSpecModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class GoodsController extends AdminBaseController
{
    public function goods_list()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $where = [];
            $num = 10;
            $where[] = ['is_delete', '=', 0];
            if (isset($data['is_on_sale']) && $data['is_on_sale']) {
                $where[] = ['is_on_sale', '=', $data['is_on_sale']];
            }
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $where[] = ['company_id', '=', $admin_info['company_id']];
            } else {
                $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
                if ($company_id) {
                    $where[] = ['company_id', '=', $company_id];
                }
            }
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', [$data['duration'][0] . ' 00:00:00', $data['duration'][1] . ' 23:59:59']];
            }
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $list = GoodsModel::with('goods_specs,company_info')
                ->where($where)
                ->order('is_on_sale desc,sort desc,create_time desc')
                ->paginate($num, false, ['page' => $page])->each(function ($item) {
                    if ($item['goods_img']) {
                        $item['goods_img'] = explode(',', $item['goods_img']);
                    }
                    return $item;
                });
            $this->success('', '', $list);
        }
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $goods_model = GoodsModel::get($data['id']);
            } else {
                $goods_model = new GoodsModel();
            }
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $goods_model->company_id = $admin_info['company_id'];
            } else {
                $goods_model->company_id = $data['company_id'];
            }
            $goods_model->goods_name = $data['goods_name'];
            $goods_model->goods_img = implode(',', $data['goods_img']);
            $goods_model->price = $data['price'];
            $goods_model->stock = $data['stock'];
            $goods_model->buy_type = $data['buy_type'];
            $goods_model->is_on_sale = $data['is_on_sale'];
            $goods_model->points = isset($data['points']) && $data['points'] ? $data['points'] : 0;
            $goods_model->gift_points = $data['gift_points'];
            $goods_model->goods_detail = $data['goods_detail'];
            $res = $goods_model->save();
            if ($res) {
                if (isset($data['goods_specs'])) {
                    foreach ($data['goods_specs'] as $key => $value) {
                        if (!isset($value['id'])) {
                            $data['goods_specs'][$key]['goods_id'] = $goods_model->id;
                        }
                    }
                    $goods_spec_model = new  GoodsSpecModel();
                    $goods_spec_model->saveAll($data['goods_specs']);
                }
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
    }

    public function change_status()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $model = GoodsModel::get($data['id']);
            if ($model) {
                $res = GoodsModel::where(['id' => $data['id']])->update([$data['field'] => $data['value']]);
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

}
