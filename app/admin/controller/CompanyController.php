<?php


namespace app\admin\controller;


use app\admin\model\CompanyModel;
use app\admin\model\UserModel;
use app\admin\model\UserRoleModel;
use cmf\controller\AdminBaseController;
use think\Validate;

class CompanyController extends AdminBaseController
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $num = 10;
            $where = [];
            $list = CompanyModel::where($where)
                ->order('create_time desc')
                ->paginate($num, false, ['page' => $page]);
            $this->success('', '', $list);
        }
        return $this->fetch();
    }

    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = new Validate([
                'name' => 'require|unique:company',
                'contact_people' => 'require',
                'contact_mobile' => 'require',
                'end_time' => 'require',
            ]);
            $validate->message([
                'name.require' => '请输入公司名称',
                'name.unique' => '公司名称存在相同',
                'contact_people.require' => '请输入登陆密码',
                'contact_mobile.require' => '请输入联系号码',
                'end_time.require' => '请输入到期时间',
            ]);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if (isset($data['id']) && $data['id']) {
                $company_model = CompanyModel::get($data['id']);
                $company_model->name = $data['name'];
                $company_model->contact_people = $data['contact_people'];
                $company_model->contact_mobile = $data['contact_mobile'];
                $company_model->end_time = $data['end_time'];
            } else {
                $company_model = CompanyModel::create($data);

            }
            $res = $company_model->save();
            if ($res) {
                if (isset($data['id']) && $data['id']) {

                } else {
                    $user_role_model = new UserRoleModel();
                    $id = UserRoleModel::order('create_time desc')->limit(1)->value('id');
                    $list = [
                        ['id' => $id + 1, 'name' => '公司', 'parent_id' => 0, 'company_id' => $company_model->id],
                        ['id' => $id + 2, 'name' => '店长', 'parent_id' => $id + 1, 'company_id' => $company_model->id],
                        ['id' => $id + 3, 'name' => '业务经理', 'parent_id' => $id + 2, 'company_id' => $company_model->id],
                        ['id' => $id + 4, 'name' => '审核员', 'parent_id' => $id + 1, 'company_id' => $company_model->id],
                        ['id' => $id + 5, 'name' => '客户经理', 'parent_id' => $id + 3, 'company_id' => $company_model->id],
                        ['id' => $id + 6, 'name' => '设计师', 'parent_id' => $id + 5, 'company_id' => $company_model->id],
                    ];
                    $user_role_model->saveAll($list, false);
                }
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $model = CompanyModel::withCount(['user_list'])->get(input('post.id'));
            if ($model) {
                if ($model['user_list_count'] > 0) {
                    $this->error('该公司下还有未删除用户');
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

    public function get_company_list()
    {
        if ($this->request->isAjax()) {
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            if ($admin_info['company_id']) {
                $this->success('', '', []);
            } else {
                $list = CompanyModel::all();
                $this->success('', '', $list);
            }

        }
    }
}
