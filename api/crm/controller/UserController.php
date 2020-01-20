<?php


namespace api\crm\controller;

use api\crm\model\ConnectLogModel;
use api\crm\model\UserCheckModel;
use api\crm\model\UserModel;
use api\crm\model\UserRoleModel;
use api\crm\model\UserRoleRelationshipModel;
use api\crm\model\UserTokenModel;
use app\admin\model\OtherModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\Exception;

class UserController extends RestBaseController
{
    protected function initialize()
    {
        parent::initialize();
        //超过设置预定天数 自动释放到公海
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $company_id = $user_info['company_id'];
        $setting = OtherModel::where('company_id', $company_id)->find();
        $array = unserialize($setting['content']);
        $time = $array['time'];
        if ($time) {
            $list = UserModel::where('last_check_time', '<=', time() - $time * 24 * 3600)
                ->where('company_id', $user_info['company_id'])
                ->where('user_type', 2)
                ->where('role_name', '')
                ->where('is_finished', 0)
                ->where('parent_id', '<>', 0)
                ->all();
            if ($list) {
                $array = [];
                foreach ($list as $key => $value) {
                    $array[$key]['customer_id'] = $value['id'];
                    $array[$key]['parent_id'] = $value['parent_id'];
                    $array[$key]['type'] = 1;
                    $array[$key]['company_id'] = $user_info['company_id'];
                    $array[$key]['remark'] = '自动释放到公海';

                    UserModel::where('id', $value['id'])->update(['parent_id' => 0]);
                }
                $log_model = new ConnectLogModel();
                $log_model->saveAll($array);
            }

        }
    }

    public function index()
    {
        $this->success('ok', 'aa');
    }

    /**
     * 获取客户经理
     */
    public function get_customer_manager_list()
    {
        $data = $this->request->param();
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
        $where = [];
        if ($keyword) {
            $where[] = ['true_name|mobile', 'like', '%' . $keyword . '%'];
        }
        $where[] = ['is_delete', '=', 0];
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        if ($user_info['role_name'] == '店长') {
            $list = UserModel::field('id,true_name,mobile')
                ->where($where)
                ->where(['company_id' => $user_info['company_id'], 'role_name' => '客户经理', 'store_id' => $user_info['store_id']])
                ->order('create_time desc')
                ->all();
        } else if ($user_info['role_name'] == '业务经理') {
            $where['parent_id'] = $user_id;
            $list = UserModel::field('id,true_name,mobile')
                ->where($where)
                ->where(['company_id' => $user_info['company_id'], 'role_name' => '客户经理', 'store_id' => $user_info['store_id']])
                ->order('create_time desc')
                ->all();
        } else {
            $list = [];
        }
        $this->success('客户经理列表', $list);
    }

    /**
     * 获取客户信息列表
     * @throws Exception\DbException
     */
    public function user_list()
    {
        $data = $this->request->param();
        $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
        $user_id = $this->getUserId();
        $current_user_info = UserModel::get($user_id);
        $user_list = [];
        $where[] = ['role_name', '=', ''];
        $where[] = ['status', '=', 0];
        $where[] = ['user_type', '=', 2];
        $where[] = ['company_id', '=', $current_user_info['company_id']];
        $where[] = ['store_id', '=', $current_user_info['store_id']];
        $where[] = ['is_finished', '=', 0];
        $where[] = ['is_delete', '=', '0'];
        if ($keyword) {
            $where[] = ['true_name|mobile', 'like', '%' . $keyword . '%'];
        }
        if (isset($data['begin_time']) && isset($data['end_time']) && $data['begin_time'] && $data['end_time']) {
            $where[] = ['create_time', 'between time', [$data['begin_time'] . ' 00:00:00', $data['end_time'] . ' 23:59:59']];
        }
        if (isset($data['customer_status_id']) && $data['customer_status_id']) {
            $where[] = ['customer_status', '=', $data['customer_status_id']];
        }
        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent_user_info = UserModel::get($data['parent_id']);
            if ($parent_user_info['role_name'] == '业务经理') {
                $cm_ids = UserModel::where('parent_id', $data['parent_id'])->where('role_name', '客户经理')->column('id');
                $ids = UserModel::where('parent_id', 'in', $cm_ids)->where('role_name', '设计师')->column('id');
                $new_array = array_merge($cm_ids, $ids);
                $new_array[] = $data['parent_id'];
                $where[] = ['parent_id', 'in', $new_array];
            } else if ($parent_user_info['role_name'] == '客户经理') {
                $ids = UserModel::where('parent_id', $data['parent_id'])->where('role_name', '设计师')->column('id');
                $ids[] = $data['parent_id'];
                $where[] = ['parent_id', 'in', $ids];
            }
        } else {
            if ($current_user_info['role_name'] == '店长') {
                $where[] = ['parent_id', '<>', 0];
            } else if ($current_user_info['role_name'] == '业务经理') {
                $cm_ids = UserModel::where('parent_id', $user_id)->where('role_name', '客户经理')->column('id');
                $ids = UserModel::where('parent_id', 'in', $cm_ids)->where('role_name', '设计师')->column('id');
                $new_array = array_merge($cm_ids, $ids);
                $new_array[] = $user_id;
                $where[] = ['parent_id', 'in', $new_array];
            } else if ($current_user_info['role_name'] == '客户经理') {
                $ids = UserModel::where('parent_id', $user_id)->where('role_name', '设计师')->column('id');
                $ids[] = $user_id;
                $where[] = ['parent_id', 'in', $ids];
            } else if ($current_user_info['role_name'] == '设计师') {
                $where[] = ['parent_id', '=', $current_user_info['id']];;
            }
        }
        $user_list = UserModel::with(['customer_status'])
            ->field('id,true_name,alias_name,source,province,city,area,address,create_time,mobile,customer_status,is_finished,last_check_time')
            ->where($where)
            ->order('last_check_time desc')
            ->paginate($num, false, ['page' => $page])
            ->each(function ($item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                if ($item['last_check_time']) {
                    $item['last_check_time'] = date('Y-m-d H:i:s', $item['last_check_time']);
                } else {
                    $item['last_check_time'] = $item['create_time'];
                }
                return $item;
            });
//        $user_list = getUserListByAliasName($user_list);
        $this->success('获取客户列表', $user_list);
    }

    /**
     * 新增业务经理 和 客户经理
     */
    public function add_service_manager()
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                if ($data['role_name'] == '业务经理') {
                    $validate_result = $this->validate($data, 'User.service_manager');
                } else {
                    $validate_result = $this->validate($data, 'User.customer_manager');
                }
                if ($validate_result !== true) {
                    throw new  Exception($validate_result);
                } else {
                    $user_id = $this->getUserId();
                    $user_info = UserModel::get($user_id);
                    $data['company_id'] = $user_info['company_id'];
                    $data['store_id'] = $user_info['store_id'];
                    $data['user_pass'] = cmf_password($data['password']);
                    $data['mobile'] = $data['user_login'];
                    $user_model = UserModel::create($data);
                    $res = $user_model->save();
                    if ($res) {
                        $user_role = UserRoleModel::where(['company_id' => $user_info['company_id'], 'name' => $data['role_name']])->find();
                        $relationship = new UserRoleRelationshipModel([
                            'user_id' => $user_model->id,
                            'user_role_id' => $user_role['id']
                        ]);
                        $relationship->save();
                        Db::commit();
                        $this->success('添加成功');
                    } else {
                        throw new Exception('添加失败');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

        }
    }

    /**
     * 更新业务经理 和 客户经理
     */
    public function update_service_manager()
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                $validate_result = $this->validate($data, 'User.edit');
                if ($validate_result !== true) {
                    throw new  Exception($validate_result);
                } else {
                    $user_info = UserModel::get($data['id']);
                    if ($user_info) {
                        $user_info->true_name = $data['true_name'];
                        $user_info->mobile = $data['user_login'];
                        $user_info->user_login = $data['user_login'];
                        $res = $user_info->save();
                        if ($res) {
                            Db::commit();
                            $this->success('更新信息成功');
                        } else {
                            throw new Exception('更新信息失败');
                        }
                    } else {
                        throw new Exception('用户不存在或已被删除');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
    }

    /**
     * 查找业务经理 和客户经理
     * @throws \think\exception\DbException
     */
    public function service_manager_list()
    {
        if ($this->request->isGet()) {
            $data = $this->request->param();
            $where = [];
            if (isset($data['keyword']) && $data['keyword']) {
                $where[] = ['true_name|mobile', 'like', '%' . $data['keyword'] . '%'];
            }
            $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 10;
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $current_user_id = $this->getUserId();
            $current_user_info = UserModel::get($current_user_id);
            $company_id = $current_user_info['company_id'];
            if ($company_id) {
                $where[] = ['company_id', '=', $company_id];
            }
            if ($current_user_info['role_name'] != '店长') {
                $where[] = ['id', '=', $current_user_id];
            }
            $where[] = ['is_delete', '=', 0];
            $where[] = ['store_id', '=', $current_user_info['store_id']];
            $user_list = UserModel::with(['user_list' => function ($query) {
                $query->where('role_name', '客户经理');
            }])
                ->field('id,mobile,true_name,role_name,parent_id')
                ->where('company_id', $company_id)
                ->where($where)
                ->where('role_name', '业务经理')
                ->paginate($num, false, ['page' => $page]);
            $user_list->visible(['user_list' => ['id', 'mobile', 'true_name', 'role_name']])->toArray();
            $this->success('获取业务经理和客户经理', $user_list);
        }
    }

    /**
     * 查看设计师列表
     */
    public function designer_list()
    {
        if ($this->request->isGet()) {
            $data = $this->request->param();
            $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 10;
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $current_user_id = $this->getUserId();
            $current_user_info = UserModel::get($current_user_id);
            $where = [];
            $order = isset($data['order']) && $data['order'] ? $data['order'] : '';
            $sort = '';
            switch ($order) {
                case 1:
                    $sort = 'create_time asc';
                    break;
                case 2:
                    $sort = 'create_time desc';
                    break;
                case 3:
                    $sort = 'customer_total_count asc';
                    break;
                case 4:
                    $sort = 'customer_total_count desc';
                    break;
                case 5:
                    $sort = 'customer_finished_count asc';
                    break;
                case 6:
                    $sort = 'customer_finished_count desc';
                    break;
                default:
                    $sort = 'create_time desc';
                    break;
            }
            $where[] = ['store_id', '=', $current_user_info['store_id']];
            $where[] = ['is_delete', '=', 0];
            if (isset($data['manager_id']) && $data['manager_id']) {
                $where[] = ['parent_id', '=', $data['manager_id']];
            } else {
                if ($current_user_info['role_name'] == '店长') {
//                    $where[] = ['parent_id', '=', $current_user_id];
                } else if ($current_user_info['role_name'] == '业务经理') {
                    $ids = UserModel::where('parent_id', $current_user_id)->column('id');
                    $where[] = ['parent_id', 'in', $ids];
                } else if ($current_user_info['role_name'] == '客户经理') {
                    $where[] = ['parent_id', '=', $current_user_id];
                }
            }
            if (isset($data['begin_time']) && isset($data['end_time']) && $data['end_time'] && $data['begin_time']) {
                $where[] = ['create_time', 'between time', [$data['begin_time'], $data['end_time']]];
            }

            $user_list = UserModel::with(['recommend_user_info'])
                ->withCount(['customer_total' => function ($query) {
                    $query->where('role_name', '');
                }])
                ->withCount(['customer_finished' => function ($query) {
                    $query->where('is_finished', 1);
                }])
                ->field('id,mobile,true_name,role_name,company_name,parent_id,img_url,card_no,birthday,hobby,family,create_time')
                ->where('company_id', $current_user_info['company_id'])
                ->where($where)
                ->where('role_name', '设计师')
                ->order($sort)
                ->paginate($num, false, ['page' => $page])->each(function ($item) {
                    if ($item['img_url']) {
                        $temp_img = explode(',', $item['img_url']);
                        $temp_array = [];
                        foreach ($temp_img as $value) {
                            $temp_array[] = $this->request->domain() . $value;
                        }
                        $item['img_url'] = $temp_array;
                    }
                    $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                    return $item;
                });
            $user_list->visible(['recommend_user_info' => ['id', 'mobile', 'true_name']])->toArray();
            $this->success('获取设计师', $user_list);
        }
    }

    public function designer_detail()
    {
        $id = input('get.id');
        $user_info = UserModel::with('recommend_user')
            ->field('id,true_name,mobile,company_name,card_no,birthday,img_url,hobby,family,parent_id')
            ->get($id);
        if (empty($user_info)) {
            $this->error('数据异常');
        }
        if ($user_info['img_url']) {
            $temp_array = [];
            $array = explode(',', $user_info['img_url']);
            foreach ($array as $value) {
                $temp_array[] = $this->request->domain() . $value;
            }
            $user_info['img_url'] = $temp_array;
        }
        $user_info->visible(['recommend_user' => ['true_name']])->toArray();
        $this->success('获取设计师详细', $user_info);
    }

    /**
     * 新增设计师
     */
    public function add_designer()
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                $validate_result = $this->validate($data, 'User.designer');
                if ($validate_result !== true) {
                    throw new  Exception($validate_result);
                } else {
                    $user_id = $this->getUserId();
                    $user_info = UserModel::get($user_id);
                    if ($user_info['role_name'] != '店长') {
                        $data['parent_id'] = $user_id;
                    } else {
                        $parent_id = $data['customer_manager_id'];
                        $customer = UserModel::get($parent_id);
                        if ($customer['role_name'] != '客户经理') {
                            throw new Exception('该用户不是客户经理');
                        }
                        $data['parent_id'] = $parent_id;
                    }
                    $data['company_id'] = $user_info['company_id'];
                    $data['store_id'] = $user_info['store_id'];
                    $data['user_pass'] = cmf_password($data['password']);
                    $data['mobile'] = $data['user_login'];
                    $model = UserModel::create($data);
                    $res = $model->save();
                    if ($res) {
                        $user_role = UserRoleModel::where(['company_id' => $user_info['company_id'], 'name' => $data['role_name']])->find();
                        $relationship = new UserRoleRelationshipModel([
                            'user_id' => $model->id,
                            'user_role_id' => $user_role['id']
                        ]);
                        $relationship->save();
                        Db::commit();
                        $this->success('新增设计师成功');
                    } else {
                        throw  new Exception('新增设计师失败');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

        }
    }

    /**
     * 更新设计师信息
     */
    public function update_designer()
    {

    }

    /**
     * 删除设计师
     */
    public function delete_designer()
    {

    }


    /**
     * 新增客户
     */
    public function add_user()
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                $validate_result = $this->validate($data, 'User.customer');
                if ($validate_result !== true) {
                    throw new Exception($validate_result);
                } else {
                    $user_id = $this->getUserId();
                    $user_info = UserModel::get($user_id);
                    if ($user_info['role_name'] == '设计师') {
                        $data['source'] = '设计师推荐';
                        $data['extend_field'] = $user_id;
                    }
                    $data['user_pass'] = cmf_password('123456');
                    $data['company_id'] = $user_info['company_id'];
                    $data['store_id'] = $user_info['store_id'];
                    $data['user_id'] = $user_id;
                    $data['status'] = 1;
                    $data['alias_name'] = getFirstCharter($data['true_name']);
                    $user_model = UserCheckModel::create($data);
                    $res = $user_model->save();
                    if ($res) {
                        Db::commit();
                        $this->success('添加客户成功');
                    } else {
                        throw  new Exception('添加客户失败');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

        }
    }

    /**
     * 报备
     */
    public function report()
    {

        $user_id = $this->getUserId();
        $current_user_info = UserModel::get($user_id);
        $where[] = ['company_id', '=', $current_user_info['company_id']];
        $where[] = ['store_id', '=', $current_user_info['store_id']];
        $where[] = ['is_delete', '=', 0];
        $data = $this->request->param();
        $begin_time = isset($data['begin_time']) && $data['begin_time'] ? $data['begin_time'] : '';
        $end_time = isset($data['end_time']) && $data['end_time'] ? $data['end_time'] : '';
        $num = isset($data['num']) && $data['num'] ? $data['num'] : 10;
        $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
        $where_time = [];
        if ($begin_time && $end_time) {
            $where_time[] = ['create_time', 'between time', [$begin_time, $end_time]];
        }
        switch ($current_user_info['role_name']) {
            case '业务经理':
                $where[] = ['role_name', 'in', ['客户经理']];
                $cm_ids = UserModel::where(['role_name' => '客户经理', 'parent_id' => $current_user_info['id']])->column('id');
                $ids = UserModel::where('parent_id', 'in', $cm_ids)->where('role_name', '设计师')->column('id');
                $new_array = array_merge($cm_ids, $ids);
                $new_array[] = $user_id;
                $where[] = ['parent_id', 'in', $new_array];
            case '店长':
                $where[] = ['role_name', 'in', ['客户经理', '业务经理']];
                break;
            default:
                $this->error('该用户无权限查看此功能');
                break;
        }
        $user_list = UserModel::field('id,true_name,role_name')
            ->where($where)
            ->order('create_time desc')
            ->paginate($num, false, ['page' => $page]);
        $data = $user_list->items();
        foreach ($data as $key => $value) {
            $data[$key]['connect_count'] = ConnectLogModel::where($where_time)
                ->where(['user_id' => $value['id'], 'status' => 1])
                ->count();

            $designer_ids = UserModel::where(['parent_id' => $value['id'], 'role_name' => '设计师'])->column('id');
            $designer_ids[] = $value['id'];

            $customer_ids = UserModel::where('parent_id', 'in', $designer_ids)
                ->where('role_name', '=', '')->column('id');
            $data[$key]['total_price'] = ConnectLogModel::where('customer_id', 'in', $customer_ids)
                ->where(['status' => 1, 'is_finished' => 1])->sum('order_price');

            $data[$key]['user_count'] = UserModel::where($where_time)
                ->where(['parent_id' => $value['id'], 'role_name' => ''])->count();
        }
        $this->success('报表', ['data' => $data, 'total' => $user_list->total()]);
    }

}
