<?php


namespace app\admin\controller;


use app\admin\model\RoleUserModel;
use app\admin\model\StoreModel;
use app\admin\model\UserModel;
use app\admin\model\UserRoleModel;
use cmf\controller\AdminBaseController;
use think\Validate;

class StoreController extends AdminBaseController
{
    public function index()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $admin_id = cmf_get_current_admin_id();
            $admin_user_info = UserModel::get($admin_id);
            $where = [];
            if ($admin_user_info['company_id']) {
                $where[] = ['company_id', '=', $admin_user_info['company_id']];
            }
            $list = StoreModel::with('company_info')->where($where)
                ->order('create_time desc')
                ->paginate(10, false, ['page' => $page]);
            $this->success('', '', $list);
        }
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = new Validate([
                'name' => 'require|unique:store',
                'contact_people' => 'require',
                'contact_mobile' => 'require',
            ]);
            $validate->message([
                'name.require' => '请输入店铺名称',
                'name.unique' => '店铺名称存在相同',
                'contact_people.require' => '请输入联系人',
                'contact_mobile.require' => '请输入联系号码'
            ]);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if (isset($data['id']) && $data['id']) {
                $company_model = StoreModel::get($data['id']);
                $company_model->name = $data['name'];
                $company_model->contact_people = $data['contact_people'];
                $company_model->contact_mobile = $data['contact_mobile'];
                $company_model->company_id = $data['company_id'];
            } else {
                if (isset($data['company_id']) && $data['company_id']) {

                } else {
                    $admin_id = cmf_get_current_admin_id();
                    $admin_user_info = UserModel::get($admin_id);
                    $data['company_id'] = $admin_user_info['company_id'];
                }
                $company_model = StoreModel::create($data);
            }
            $res = $company_model->save();
            if ($res) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    public function get_store_by_company_id()
    {
        $data = $this->request->param();
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $list = StoreModel::where('company_id', $company_id)->all();
        $role_list = UserRoleModel::where('company_id', $company_id)->all();
        $manager_list = UserModel::where(['company_id' => $company_id, 'role_name' => '业务经理'])
            ->where(['user_type' => 2, 'is_delete' => 0])
            ->order('create_time desc')
            ->all()->each(function ($item){
                $item['name'] = $item['true_name'] . '(' . $item['role_name'] . ')';
                return $item;
            });
        $this->success($company_id, '', ['store_list' => $list, 'role_list' => $role_list, 'manager_list' => $manager_list]);
    }

    public function delete()
    {
        $model = StoreModel::withCount(['user_list' => function ($query) {
            $query->where('is_delete', 0);
        }])->get(input('post.id'));
        if ($model) {
            if ($model['user_list_count'] > 0) {
                $this->error('该店铺下还有未删除用户');
            }
            $res = $model->delete();
            if ($res) {
                $this->success('ok');
            } else {
                $this->error('fail');
            }
        } else {
            $this->error('数据异常');
        }
    }
}
