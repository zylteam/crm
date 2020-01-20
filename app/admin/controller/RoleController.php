<?php


namespace app\admin\controller;


use api\crm\model\UserCheckModel;
use app\admin\model\CompanyModel;
use app\admin\model\MobileAuthRuleModel;
use app\admin\model\RoleUserModel;
use app\admin\model\StoreModel;
use app\admin\model\UserModel;
use app\admin\model\UserRoleModel;
use app\admin\model\UserRoleRelationshipModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Env;
use think\Validate;
use tree\Tree;
use PHPExcel;

class RoleController extends AdminBaseController
{
    protected $company_id = '';

    protected function initialize()
    {
        $admin_id = cmf_get_current_admin_id();
        $user_info = UserModel::get($admin_id);
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        $this->assign('admin_info', $user_info);
        if ($user_info['company_id']) {
            $this->company_id = $user_info['company_id'];
        }
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            if ($this->company_id) {
                $company_list = CompanyModel::with(['user_role_list'])
                    ->where('id', $this->company_id)
                    ->order('create_time desc')
                    ->paginate(10, false, ['page' => $page])->each(function ($item) {
                        $list = $item['user_role_list']->toArray();
                        if ($list) {
                            $list_tree = list_to_tree($list, 'id', 'parent_id', 'children');
                            $item['list'] = $list_tree;
                        }
                        return $item;
                    });
            } else {
                $company_list = CompanyModel::with(['user_role_list'])
                    ->order('create_time desc')
                    ->paginate(10, false, ['page' => $page])->each(function ($item) {
                        $list = $item['user_role_list']->toArray();
                        if ($list) {
                            $list_tree = list_to_tree($list, 'id', 'parent_id', 'children');
                            $item['list'] = $list_tree;
                        }
                        return $item;
                    });
            }
            $this->success('', '', $company_list);
        }
        return $this->fetch();
    }

    public function get_role()
    {
        $data = $this->request->param();
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $admin_id = cmf_get_current_admin_id();
        $admin_info = UserModel::get($admin_id);
        $where = [];
        if ($admin_info['company_id']) {
            $where['company_id'] = $admin_info['company_id'];
        } else {
            if ($company_id) {
                $where['company_id'] = $company_id;
            } else {
                $this->success('', '', []);
            }
        }
        $list = UserRoleModel::where($where)->all();
        $this->success('', '', $list);
    }

    public function update_role()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $user_role_model = UserRoleModel::get($data['id']);
                $user_role_model->name = $data['name'];
            } else {
                $user_role_model = UserRoleModel::create($data);
            }
            $res = $user_role_model->save();
            if ($res) {
                $this->success('更新成功');
            } else {
                $this->error('更新失败');
            }
        }
    }

    public function update_user()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = new Validate([
                'true_name' => 'require',
                'user_login' => 'require|unique:user',
                'user_pass' => 'require',
            ]);
            $validate->message([
                'true_name.require' => '请输入店长姓名',
                'user_login.require' => '手机号不能为空',
                'user_login.unique' => '该手机号码已使用',
                'user_pass.require' => '请输入账号密码',
            ]);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $admin_id = cmf_get_current_admin_id();
            $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : '';
            $user_info = UserModel::get($admin_id);
            if ($company_id) {
                $data['company_id'] = $company_id;
            } else {
                if ($user_info['company_id']) {
                    $company_id = $user_info['company_id'];
                    $data['company_id'] = $company_id;
                } else {
                    $this->error('请选择公司');
                }
            }

            if (isset($data['id']) && $data['id']) {
                $user_model = UserModel::get($data['id']);
                if ($data['user_pass'] != $user_model['user_pass']) {
                    $user_model->user_pass = cmf_password($data['user_pass']);
                }
                $user_model->true_name = $data['true_name'];
                $user_model->mobile = $data['user_login'];
                $user_model->user_login = $data['user_login'];
                $user_model->company_id = $company_id;
                $user_model->store_id = $data['store_id'];
                $user_model->role_name = $data['role_name'];
                $user_model->parent_id = $data['parent_id'];
            } else {
                $data['user_type'] = 2;
                $data['mobile'] = $data['user_login'];
                $data['user_pass'] = cmf_password($data['user_pass']);
                $user_model = UserModel::create($data);
            }
            $res = $user_model->save();
            if ($res) {
                if (isset($data['id']) && $data['id']) {
                    $user_id = $data['id'];
                } else {
                    $user_id = $user_model->id;
                }
                if ($user_model['role_name']) {
                    $relation = UserRoleRelationshipModel::where(['user_id' => $user_id])->find();
                    $user_role = UserRoleModel::where(['name' => $user_model['role_name'], 'company_id' => $company_id])->find();
                    if ($relation) {
                        $relation->user_role_id = $user_role['id'];
                    } else {
                        $relation = new  UserRoleRelationshipModel([
                            'user_role_id' => $user_role['id'],
                            'user_id' => $user_id
                        ]);
                    }
                    $res = $relation->save();
                }
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
        }
    }

    public function user_list()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) ? intval($data['page']) : 1;
            $admin_id = cmf_get_current_admin_id();
            $where[] = ['user_type', '=', 2];
            $where[] = ['role_name', '=', ''];
            $where[] = ['is_delete', '=', 0];
            $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
            if ($company_id) {
                $where[] = ['company_id', '=', $company_id];
            } else {
                $company_id = UserModel::get($admin_id)['company_id'];
                if ($company_id) {
                    $where[] = ['company_id', '=', $company_id];
                }
            }
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', [$data['duration'][0] . ' 00:00:00', $data['duration'][1] . ' 23:59:59']];
            }
            if (isset($data['manager_id']) && $data['manager_id']) {
                $where[] = ['parent_id', '=', $data['manager_id']];
            }
            if (isset($data['customer_status_id']) && $data['customer_status_id']) {
                $where[] = ['customer_status', '=', $data['customer_status_id']];
            }
            $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
            if ($keyword) {
                $where[] = ['true_name|user_login|mobile', 'like', '%' . $keyword . '%'];
            }
            $user_list = UserModel::with(['relation_ship' => ['role_info'], 'recommend_user_info', 'company_info', 'store_info', 'customer_status'])
                ->where($where)->order('create_time desc')
                ->paginate(10, false, ['page' => $page])->each(function ($item) {
                    if ($item['source'] == '设计师推荐') {
                        $item['extend_field'] = UserModel::get($item['extend_field'])['true_name'];
                    }
                    return $item;
                });
            $this->success('', '', $user_list);
        }
        return $this->fetch();
    }

    public function manager_list()
    {
        $admin_id = cmf_get_current_admin_id();
        $admin_user_info = UserModel::get($admin_id);
        $store_data = StoreModel::where('company_id', $admin_user_info['company_id'])->all();
        $this->assign('store_data', $store_data);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) ? intval($data['page']) : 1;
            $admin_id = cmf_get_current_admin_id();
            $where[] = ['user_type', '=', 2];
            $where[] = ['role_name', '<>', ''];
            $where[] = ['is_delete', '=', 0];
            $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
            if ($company_id) {
                $where[] = ['company_id', '=', $company_id];
            } else {
                $company_id = UserModel::get($admin_id)['company_id'];
                if ($company_id) {
                    $where[] = ['company_id', '=', $company_id];
                }
            }
            $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
            if ($keyword) {
                $where[] = ['true_name|user_login|mobile', 'like', '%' . $keyword . '%'];
            }
            $user_list = UserModel::with(['relation_ship' => ['role_info'], 'recommend_user_info', 'company_info', 'store_info'])
                ->where($where)->order('create_time desc')
                ->paginate(10, false, ['page' => $page])->each(function ($item) {
                    if ($item['source'] == '设计师推荐') {
                        $item['extend_field'] = UserModel::get($item['extend_field'])['true_name'];
                    }
                    return $item;
                });
            $this->success('', '', $user_list);
        }
        return $this->fetch();
    }

    public function get_manager_list()
    {
        $data = $this->request->param();
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $type = isset($data['type']) && $data['type'] ? $data['type'] : 1;
        $admin_id = cmf_get_current_admin_id();
        $admin_info = UserModel::get($admin_id);

        if ($admin_info['company_id']) {
            $where[] = ['company_id', '=', $admin_info['company_id']];
        } else {
            if ($company_id) {
                $where[] = ['company_id', '=', $company_id];
            }
        }
        if ($type == 1) {
            $where[] = ['role_name', 'in', ['客户经理', '业务经理']];
        } else {
            $where[] = ['role_name', 'in', ['业务经理']];
        }
        $where[] = ['user_type', '=', 2];
        $user_list = UserModel::field('id,true_name,role_name')
            ->order('create_time desc')->where($where)->all()->each(function ($item) {
                $item['name'] = $item['true_name'] . '(' . $item['role_name'] . ')';
                return $item;
            });
        $this->success('获取业务经理和客户经理', '', $user_list);
    }

    public function user_check()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) ? intval($data['page']) : 1;
            $admin_id = cmf_get_current_admin_id();
            $company_id = UserModel::get($admin_id)['company_id'];
            $where = [];
            if ($company_id) {
                $where['company_id'] = $company_id;
            }
            $user_list = UserCheckModel::with(['user_info', 'company_info'])
                ->where($where)->order('create_time desc')
                ->paginate(10, false, ['page' => $page])->each(function ($item) {
                    if ($item['source'] == '设计师推荐') {
                        $item['extend_field'] = UserModel::get($item['extend_field'])['true_name'];
                    }
                    return $item;
                });
            $this->success('', '', $user_list);
        }
        return $this->fetch();
    }

    /**
     * 角色权限菜单
     */
    public function oauth()
    {
        if ($this->request->isAjax()) {
            $list = MobileAuthRuleModel::all()->toArray();
            $list_to_tree = list_to_tree($list, 'id', 'parent_id', 'children');
            $this->success('', '', $list_to_tree);
        }
        return $this->fetch();
    }

    public function get_all_oauth()
    {
        $list = [];
        $result = MobileAuthRuleModel::where('parent_id', 0)->all()->toArray();
        foreach ($result as $key => $value) {
            array_push($list, $value);
            $two_children = MobileAuthRuleModel::where('parent_id', $value['id'])->all()->toArray();
            if ($two_children) {
                foreach ($two_children as $t_k => $t_v) {
                    $t_v['name'] = '-' . $t_v['name'];
                    array_push($list, $t_v);
                    $three_children = MobileAuthRuleModel::where('parent_id', $t_v['id'])->all()->toArray();
                    if ($three_children) {
                        foreach ($three_children as $k => $v) {
                            $v['name'] = '--' . $v['name'];
                            array_push($list, $v);
                        }
                    }
                }
            }
        }
        $this->success('', '', $list);
    }

    public function set_user_auth()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $user_role = UserRoleModel::get($data['user_role_id']);
            if ($user_role->auths) {
                $user_role->auths()->detach();
            }
            $auth = $data['auth'];
            $rule_name_list = [];
            foreach ($auth as $key => $value) {
                $rule_name_list[] = $value['id'];
            }
            $auth_model = MobileAuthRuleModel::get(1);
            $user_role->auths()->attach($rule_name_list);
            $this->success('ok', '', ['aa' => $auth_model, 'bb' => $auth]);
        }
    }

    public function update_auth()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $model = MobileAuthRuleModel::get($data['id']);
                $model->name = $data['name'];
                $model->url = $data['url'];
                $model->api_url = $data['api_url'];
                $model->icon = $data['icon'];
                $model->parent_id = $data['parent_id'];
            } else {
                $model = MobileAuthRuleModel::create($data);
            }
            $res = $model->save();
            if ($res) {
                $this->success('ok');
            } else {
                $this->error('fail');
            }
        }
    }

    public function delete_oauth()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $model = MobileAuthRuleModel::get($data['id']);
            if ($model) {
                $children = MobileAuthRuleModel::where('parent_id', $model['id'])->count();
                if ($children > 0) {
                    $this->error('该分类下还有未删除的权限');
                } else {
                    $res = $model->delete();
                    if ($res) {
                        $this->success('ok');
                    } else {
                        $this->error('fail');
                    }
                }
            } else {
                $this->error('数据异常');
            }
        }
    }

    public function get_user_auth()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $auth_list = [];
            if ($data['company_id']) {
                $user_role = UserRoleModel::with('company_info')->where(['company_id' => $data['company_id']])->get($data['user_role_id']);
                $auth_list = $user_role->auths;
            }
            $this->success('', '', $auth_list);
        }
    }

    public function delete_user()
    {
        $id = input('id');
        $user_info = UserModel::get($id);
        if (empty($user_info)) {
            $this->error('用户不存在或已删除');
        }
        $user_info->is_delete = 1;
        $user_info->user_login = $user_info['user_login'] . '(已删除)';
        $user_info->mobile = $user_info['mobile'] . '(已删除)';
        $user_info->true_name = $user_info['true_name'] . '(已删除)';
        $res = $user_info->save();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function export()
    {
        $data = $this->request->param();
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $admin_id = cmf_get_current_admin_id();
        $where[] = ['user_type', '=', 2];
        $where[] = ['role_name', '=', ''];
        $where[] = ['is_delete', '=', 0];
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        if ($company_id) {
            $where[] = ['company_id', '=', $company_id];
        } else {
            $company_id = UserModel::get($admin_id)['company_id'];
            if ($company_id) {
                $where[] = ['company_id', '=', $company_id];
            }
        }
        if (isset($data['duration']) && $data['duration']) {
            $where[] = ['create_time', 'between time', [$data['duration'][0] . ' 00:00:00', $data['duration'][1] . ' 23:59:59']];
        }
        if (isset($data['manager_id']) && $data['manager_id']) {
            $where[] = ['parent_id', '=', $data['manager_id']];
        }
        if (isset($data['customer_status_id']) && $data['customer_status_id']) {
            $where[] = ['customer_status', '=', $data['customer_status_id']];
        }
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
        if ($keyword) {
            $where[] = ['true_name|user_login|mobile', 'like', '%' . $keyword . '%'];
        }
        $user_list = UserModel::with(['relation_ship' => ['role_info'], 'recommend_user_info', 'company_info', 'store_info', 'customer_status'])
            ->where($where)->order('create_time desc')
            ->all()->each(function ($item) {
                if ($item['source'] == '设计师推荐') {
                    $item['extend_field'] = UserModel::get($item['extend_field'])['true_name'];
                }
                if ($item['create_time']) {
                    $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                }
                if ($item['last_check_time']) {
                    $item['last_check_time'] = date('Y-m-d H:i:s', $item['last_check_time']);
                }
                return $item;
            });
        $xlsName = "Excel";
        $xlsCell = array(
            array('id', '序号'),
            array('true_name', '姓名'),
            array('mobile', '手机号码'),
            array('customer_status_name', '客户状态'),
            array('province', '省'),
            array('city', '市'),
            array('area', '区'),
            array('address', '地址'),
            array('create_time', '添加时间'),
            array('last_check_time', '最后跟进时间'),
            array('recommend_user_info', '上级', 'true_name'),
        );
        $this->outputAdmins('user', $xlsCell, $user_list);
    }

    public function outputAdmins($fileName = '文件名', $headArr, $data)
    {
        $fileName .= "_" . date("Ymd_His", time()) . ".xls";
        $objPHPExcel = new \PHPExcel();
        // Set document properties （设置文档属性）
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        // 设置头信息
        $objPHPExcel->setActiveSheetIndex(0);
        $key = ord('A');
        foreach ($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->getActiveSheet()->setCellValue($colum . '1', $v[1]);
            $key += 1;
        }
        // 获取管理员全部数据

        $column = 2;
        foreach ($data as $key => $rows) { // 行写入
            $span = ord("A");
            foreach ($headArr as $value) {
                if ($rows[$value[0]]) {
                    if (isset($value[2])) {
                        $objPHPExcel->getActiveSheet()->setCellValue(chr($span) . $column, $rows[$value[0]][$value[2]]);
                    } else {
                        $objPHPExcel->getActiveSheet()->setCellValue(chr($span) . $column, $rows[$value[0]]);
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue(chr($span) . $column, '');
                }

                $span++;
            }
            $column++;
        }
        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save(str_replace('.php', '.xls', 'upload/default/' . date("Ymd", time()) . '/' . $fileName));
        $this->success('导出成功', $this->request->domain() . '/upload/default/' . date("Ymd", time()) . '/' . $fileName);
    }


}
