<?php


namespace api\crm\controller;


use api\crm\model\ConnectLogModel;
use api\crm\model\CustomerStatusModel;
use api\crm\model\IntentionTagModel;
use api\crm\model\UserCheckModel;
use api\crm\model\UserModel;
use api\user\model\CommentModel;
use cmf\controller\RestBaseController;
use mindplay\demo\Form;
use think\Db;
use think\Exception;

class CustomerController extends RestBaseController
{
    public function index()
    {
        $this->success('ok');
    }

    /**
     * 获取未审核用户
     */
    public function check_list()
    {
        $data = $this->request->param();
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $where[] = ['company_id', '=', $user_info['company_id']];
        $where[] = ['store_id', '=', $user_info['store_id']];
        $where[] = ['status', '=', 1];
        if ($keyword) {
            $where[] = ['true_name|user_login', 'like', '%' . $keyword . '%'];
        }
        $user_list = UserCheckModel::with('user_info')
            ->field('id,true_name,mobile,create_time,province,city,area,address,user_id,source,extend_field,alias_name')
            ->where($where)
            ->order('create_time desc')
            ->all()->each(function ($item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                if ($item['source'] == '设计师推荐') {
                    $item['designer'] = UserModel::field('id,true_name,role_name')->get(intval($item['extend_field']));
                }
                return $item;
            });
        $user_list->Visible(['user_info' => ['true_name']])->toArray();
        $user_list = getUserListByAliasName($user_list);
        $this->success('获取未审核的用户', $user_list);
    }

    public function get_check_user_info()
    {
        $data = $this->request->param();
        $user_id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
        if (empty($user_id)) {
            $this->error('请传入审核用户id');
        }
        $user_model = UserCheckModel::with('user_info')->where(['id' => $user_id, 'status' => 1])->find();
        if ($user_model) {
            $user_model->visible(['user_info' => ['id', 'true_name']])->toArray();
            $user_model['create_time'] = date('Y-m-d H:i:s', $user_model['create_time']);
            if ($user_model['source'] == '设计师推荐') {
                $user_info = UserModel::field('id,true_name')->get($user_model['extend_field']);
                $user_model['extend_field'] = $user_info['true_name'];
            }
            if ($user_model['img_url']) {
                $temp_array = [];
                $array = explode(',', $user_model['img_url']);
                foreach ($array as $value) {
                    $temp_array[] = $this->request->domain() . $value;
                }
                $user_model['img_url'] = $temp_array;
            }
            $this->success('获取用户信息', $user_model);
        } else {
            $this->error('用户不存在或审核状态已更改');
        }
    }

    public function check()
    {
        if ($this->request->isPost()) {
            try {
                Db::startTrans();
                $data = $this->request->param();
                $user_id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
                $status = isset($data['status']) && $data['status'] ? $data['status'] : 0;
                if ($status == 2) {
                    $check_user_info = UserCheckModel::with('user_info')
                        ->where(['id' => $user_id, 'status' => 1])
                        ->find();
                    if (empty($check_user_info)) {
                        throw  new Exception('用户不存在或审核状态已更改');
                    } else {
                        $check_user_info->status = 2;
                        $res = $check_user_info->save();
                        if ($res) {
                            Db::commit();
                            $this->success('更新成功');
                        } else {
                            throw  new  Exception('更新失败');
                        }
                    }
                } else {
                    if (empty($user_id)) {
                        throw new  Exception('请传入未审核用户id');
                    }
                    $check_user_info = UserCheckModel::with('user_info')
                        ->where(['id' => $user_id, 'status' => 1])
                        ->find();
                    if ($check_user_info) {
                        $current_user_id = $this->getUserId();
                        $current_user_info = UserModel::get($current_user_id);
                        $parent_id = 0;
                        if ($check_user_info['source'] == '设计师推荐') {
                            $parent_id = $check_user_info['extend_field'];
                        } else {
                            $parent_id = $check_user_info['user_id'];
                        }

                        $user_model = UserModel::create([
//                            'user_login' => $check_user_info['user_login'],
                            'company_id' => $check_user_info['company_id'],
                            'store_id' => $check_user_info['store_id'],
                            'user_type' => 2,
                            'alias_name' => $check_user_info['alias_name'],
                            'sex' => $check_user_info['sex'],
                            'birthday' => $check_user_info['birthday'],
                            'user_status' => 1,
                            'user_pass' => cmf_password('123456'),
                            'mobile' => $check_user_info['mobile'],
                            'source' => $check_user_info['source'],
                            'extend_field' => $check_user_info['extend_field'],
                            'true_name' => $check_user_info['true_name'],
                            'hobby' => $check_user_info['hobby'],
                            'family' => $check_user_info['family'],
                            'img_url' => $check_user_info['img_url'],
                            'province' => $check_user_info['province'],
                            'city' => $check_user_info['city'],
                            'area' => $check_user_info['area'],
                            'address' => $check_user_info['address'],
                            'customer_status' => 15,
                            'user_id' => $current_user_id,
                            'status' => 0,
                            'parent_id' => $parent_id,
                            'last_check_time' => time(),
                        ]);
                        $res1 = $user_model->save();
                        $connect_log = new ConnectLogModel([
                            'user_id' => $user_id,
                            'company_id' => $current_user_info['company_id'],
                            'customer_id' => $user_model->id,
                            'parent_id' => $parent_id,
                            'remark' => '用户审核记录',
                            'type' => 3
                        ]);
                        $connect_log->save();
                        $check_user_info->status = 0;
                        $res = $check_user_info->save();
                        if ($res && $res1) {
                            Db::commit();
                            $this->success('更改用户审核状态成功');
                        } else {
                            throw new Exception('更改用户审核状态失败');
                        }
                    } else {
                        throw  new Exception('用户不存在或审核状态已更改');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
    }

    /**
     * 查找用户详细
     */
    public function search_user_detail()
    {
        $id = input('get.id');
        $user_info = UserModel::get($id);
        if ($user_info) {
            $user_info->visible(['recommend_user' => ['true_name']])->toArray();
            if ($user_info['img_url']) {
                $temp_array = [];
                $array = explode(',', $user_info['img_url']);
                foreach ($array as $item) {
                    $temp_array[] = $this->request->domain() . $item;
                }
                $user_info['img_url'] = $temp_array;
            }
            if ($user_info['source'] == '设计师推荐') {
                $user_info['extend_field'] = UserModel::get($user_info['extend_field'])['true_name'];
            }
            $this->success('获取用户信息成功', $user_info);
        } else {
            $this->error('数据异常');
        }
    }

    public function search_user_log()
    {
        $id = input('get.id');
        $user_info = UserModel::field('id,mobile,true_name,source,province,city,area,address')
            ->with(['connect_log' => function ($query) {
                $query->with(['progress_status', 'customer_status', 'user_info', 'recommend_user'])->order('create_time desc');
            }])->get($id);
        if ($user_info) {
            $user_info->connect_log->each(function ($item) {
                if ($item['img_url']) {
                    $temp_array = [];
                    $array = explode(',', $item['img_url']);
                    foreach ($array as $value) {
                        $temp_array[] = $this->request->domain() . $value;
                    }
                    $item['img_url'] = $temp_array;
                }
                if ($item['order_pic']) {
                    $temp_array = [];
                    $array = explode(',', $item['order_pic']);
                    foreach ($array as $value) {
                        $temp_array[] = $this->request->domain() . $value;
                    }
                    $item['order_pic'] = $temp_array;
                }
                if ($item['tag_ids']) {
                    $temp_tag = [];
                    $array = explode(',', $item['tag_ids']);
                    foreach ($array as $value) {
                        $temp_tag[] = IntentionTagModel::get($value)['name'];
                    }
                    $item['tag_ids'] = $temp_tag;
                }
                $item['create_time'] = date('Y/m/d', $item['create_time']);
                return $item;
            });

            $this->success('获取用户信息成功', $user_info);
        } else {
            $this->error('数据异常', $id);
        }
    }

    /**
     * 获取跟进记录
     * @throws \think\exception\DbException
     */
    public function check_log_list()
    {
        $data = $this->request->param();
        $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $log = ConnectLogModel::with('user_info,customer_info,progress_status,customer_status')
            ->order('create_time desc')
            ->where('status', 0)
            ->where('company_id', $user_info['company_id'])
            ->where('store_id', $user_info['store_id'])
            ->paginate($num, false, ['page' => $page])->each(function ($item) {
                if ($item['img_url']) {
                    $temp_array = [];
                    $array = explode(',', $item['img_url']);
                    foreach ($array as $value) {
                        $temp_array[] = $this->request->domain() . $value;
                    }
                    $item['img_url'] = $temp_array;
                }
                if ($item['order_pic']) {
                    $temp_array = [];
                    $array = explode(',', $item['order_pic']);
                    foreach ($array as $value) {
                        $temp_array[] = $this->request->domain() . $value;
                    }
                    $item['order_pic'] = $temp_array;
                }
                if ($item['tag_ids']) {
                    $temp_tag = [];
                    $array = explode(',', $item['tag_ids']);
                    foreach ($array as $value) {
                        $temp_tag[] = IntentionTagModel::get($value)['name'];
                    }
                    $item['tag_ids'] = $temp_tag;
                }
                $item['create_time'] = date('Y/m/d', $item['create_time']);
                return $item;
            });
        $this->success('获取用户信息成功', $log);
    }

    /**
     * 审核跟进记录
     */
    public function check_record()
    {
        if ($this->request->post()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                $id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
                $status = isset($data['status']) && $data['status'] ? $data['status'] : 0;
                $connect_log = ConnectLogModel::with(['customer_info'])->where(['id' => $id, 'status' => 0])->find();
                if (empty($status)) {
                    throw new Exception('请输入审核状态');
                }
                if (empty($connect_log)) {
                    throw new Exception('记录不存在或已处理');
                } else {
                    $res = '';
                    if ($status == 2) {
                        //审核失败
                        $connect_log->status = 2;
                        $res = $connect_log->save();
                    } else {
                        $customer_status = CustomerStatusModel::get($connect_log['customer_status']);
                        if ($customer_status['name'] == '成交') {
                            $connect_log->is_finished = 1;
                            $connect_log->customer_info->is_finished = 1;
                        } else if ($customer_status['name'] == '丢单客户') {
                            $connect_log->customer_info->parent_id = 0;
                        }
                        $connect_log->customer_info->customer_status = $connect_log['customer_status'];
                        $connect_log->customer_info->last_check_time = time();
                        $connect_log->customer_info->save();
                        $connect_log->status = 1;
                        $res = $connect_log->save();
                    }
                    if ($res) {
                        Db::commit();
                        $this->success('更新成功', $connect_log);
                    } else {
                        throw new Exception('更新失败');
                    }
                }
            } catch (Exception $e) {
                DB::rollback();
                $this->error($e->getMessage());
            }
        }
    }

    public function check_customer_detail()
    {
        $id = input('get.id');
        $user_info = UserCheckModel::get($id);
        if ($user_info) {
            $user_info->visible(['recommend_user' => ['true_name']])->toArray();
            if ($user_info['img_url']) {
                $temp_array = [];
                $array = explode(',', $user_info['img_url']);
                foreach ($array as $item) {
                    $temp_array[] = $this->request->domain() . $item;
                }
                $user_info['img_url'] = $temp_array;
            }
            if ($user_info['source'] == '设计师推荐') {
                $user_info['extend_field'] = UserModel::get($user_info['extend_field'])['true_name'];
            }
            $this->success('获取用户信息成功', $user_info);
        } else {
            $this->error('数据异常');
        }
    }

    /**
     * 释放到公海
     */
    public function release()
    {
        $data = $this->request->param();
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $ids = isset($data['ids']) && $data['ids'] ? $data['ids'] : 0;
        $user_list = UserModel::where('id', 'in', $ids)->where('is_finished', 0)->all();
        if ($user_list) {
            $array = [];
            $connect_log = new ConnectLogModel();
            foreach ($user_list as $key => $value) {
                $array[$key]['user_id'] = $user_id;
                $array[$key]['customer_id'] = $value['id'];
                $array[$key]['parent_id'] = $value['parent_id'];
                $array[$key]['type'] = 1;
                $array[$key]['company_id'] = $user_info['company_id'];
                $array[$key]['remark'] = '店长释放客户到公海';
            }
            $connect_log->saveAll($array);
        }

        $res = UserModel::where('id', 'in', $ids)->where('is_finished', 0)->update(['parent_id' => 0]);
        if ($res) {
            $this->success('释放客户到公海成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 公海客户列表
     */
    public function common_user()
    {
        $data = $this->request->param();
        $user_id = $this->getUserId();
        $current_user_info = UserModel::get($user_id);
        $customer_status = isset($data['customer_status']) && $data['customer_status'] ? $data['customer_status'] : '';
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';
        $where[] = ['role_name', '=', ''];
        $where[] = ['delete_time', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['user_type', '=', 2];
        $where[] = ['company_id', '=', $current_user_info['company_id']];
        $where[] = ['parent_id', '=', 0];
        if ($customer_status) {
            $where[] = ['customer_status', '=', $customer_status];
        }
        if ($keyword) {
            $where[] = ['true_name|mobile', 'like', '%' . $keyword . '%'];
        }
        if ($current_user_info['role_name'] == '店长') {

        } else if ($current_user_info['role_name'] == '业务经理') {
            $cm_ids = UserModel::where('parent_id', $user_id)->where('role_name', '客户经理')->column('id');
            $ids = UserModel::where('parent_id', 'in', $cm_ids)->where('role_name', '设计师')->column('id');
            $new_array = array_merge($cm_ids, $ids);
            $new_array[] = $user_id;
            $where[] = ['parent_id', 'in', $new_array];
        } else if ($current_user_info['role_name'] == '客户经理') {
            $ids = UserModel::where('parent_id', $user_id)->column('id');
            $where[] = ['parent_id', 'in', $ids];
        } else if ($current_user_info['role_name'] == '设计师') {
            $where['parent_id'] = $current_user_info['id'];
        }
        $user_list = UserModel::with(['customer_status'])
            ->field('id,true_name,alias_name,source,province,city,area,address,create_time,mobile,customer_status,is_finished,update_time')
            ->where($where)
            ->order('create_time desc')
            ->all()->each(function ($item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                return $item;
            });;
        $user_list = getUserListByAliasName($user_list);
        $this->success('获取客户列表', $user_list);
    }

    /**
     * 转移公海客户
     */
    public function transfer_user()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $user_id = $this->getUserId();
            $user_info = UserModel::get($user_id);
            $customer_id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
            $manager_id = isset($data['manager_id']) && $data['manager_id'] ? $data['manager_id'] : 0;
            $manager_info = UserModel::get($manager_id);
            $customer_info = UserModel::get($customer_id);
            if (empty($customer_info)) {
                $this->error('用户不存在或已删除');
            }
            if ($customer_info['parent_id']) {
                $this->error('该用户已有人负责');
            } else {
                $connect_log = new  ConnectLogModel([
                    'user_id' => $user_id,
                    'company_id' => $user_info['company_id'],
                    'customer_id' => $customer_id,
                    'remark' => '转移公海客户',
                    'parent_id' => $manager_id,
                    'type' => 2
                ]);
                $connect_log->save();
                $customer_info->store_id = $manager_info['store_id'];
                $customer_info->parent_id = $manager_id;
                $res = $customer_info->save();
                if ($res) {
                    $this->success('转移成功');
                } else {
                    $this->error('转移失败');
                }
            }
        }
    }

    /**
     * 已完成客户
     */
    public function finished_user()
    {
        $data = $this->request->param();
        $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $keyword = isset($data['keyword']) && $data['keyword'] ? $data['keyword'] : '';

        $user_id = $this->getUserId();
        $current_user_info = UserModel::get($user_id);
        $where[] = ['role_name', '=', ''];
        $where[] = ['delete_time', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['user_type', '=', 2];
        $where[] = ['store_id', '=', $current_user_info['store_id']];
        $where[] = ['company_id', '=', $current_user_info['company_id']];
        $where[] = ['is_finished', '=', 1];
        $where[] = ['is_delete', '=', 0];
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
                $where[] = ['parent_id', 'in', $current_user_info['id']];
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
        $this->success('获取已完成客户列表', $user_list);
    }

    /**
     * 客户统计
     */
    public function customer_statistics()
    {
        $data = $this->request->param();
        $type = isset($data['type']) && $data['type'] ? $data['type'] : 1;
        $user_id = $this->getUserId();
        $current_user_info = UserModel::get($user_id);
        $where = [];
        switch ($type) {
            case 1:
                //未完成客户统计
                $where[] = ['type', '=', 0];
                break;
            case 2:
                //已完成客户统计
                $where[] = ['type', '=', 1];
                break;
            case 3:
                if ($current_user_info['role_name'] != '店长') {
                    $this->error('无权限查看');
                }
                $where[] = ['type', '=', 0];
                break;
            default:
                $this->error('数据异常');
                break;
        }
        $where[] = ['company_id', '=', $current_user_info['company_id']];

        $customer_status = CustomerStatusModel::field('id,name')
            ->withCount(['customer_list' => function ($query) {
                $data = $this->request->param();
                $begin_time = isset($data['begin_time']) && $data['begin_time'] ? $data['begin_time'] : '';
                $end_time = isset($data['end_time']) && $data['end_time'] ? $data['end_time'] : '';
                if ($begin_time && $end_time) {
                    $where[] = ['create_time', 'between time', [$begin_time, $end_time]];
                }
                $type = isset($data['type']) && $data['type'] ? $data['type'] : 1;
                $user_id = $this->getUserId();
                $user_info = UserModel::get($user_id);
                $parent_id_where = [];
                switch ($user_info['role_name']) {
                    case '设计师':
                        $parent_id_where = ['parent_id', '=', $user_id];
                        break;
                    case '客户经理':
                        $ids = UserModel::where('parent_id', $user_id)->column('id');
                        $parent_id_where = ['parent_id', 'in', $ids];
                        break;
                    case '业务经理':
                        $cm_ids = UserModel::where('parent_id', $user_id)->where('role_name', '客户经理')->column('id');
                        $ids = UserModel::where('parent_id', 'in', $cm_ids)->where('role_name', '设计师')->column('id');
                        $new_array = array_merge($cm_ids, $ids);
                        $new_array[] = $user_id;
                        $parent_id_where = ['parent_id', 'in', $new_array];
                        break;
                    case '店长':
                        break;
                    default:
                        break;
                }
                switch ($type) {
                    case 1:
                        //未完成客户统计
                        $where[] = ['is_finished', 'in', 0];
                        $where[] = ['store_id', '=', $user_info['store_id']];
                        break;
                    case 2:
                        //已完成客户统计
                        $where[] = ['is_finished', '=', 1];
                        $where[] = ['store_id', '=', $user_info['store_id']];
                        break;
                    case 3:
                        //公海客户统计
                        $where[] = ['is_finished', '=', 0];
                        $parent_id_where = ['parent_id', '=', 0];
                        break;
                    default:
                        break;
                }
                $where[] = ['role_name', '=', ''];
                $where[] = ['status', '=', 0];
                $where[] = ['user_type', '=', 2];
                $where[] = ['is_delete', '=', 0];
                if ($parent_id_where) {
                    $where[] = $parent_id_where;
                }
                $query->where($where);
            }])
            ->where($where)
            ->all();
        $this->success('', ['aa' => $customer_status]);
    }

    public function delete_common_user()
    {
        $data = $this->request->param();
        $id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
        $user_info = UserModel::get($id);
        if (empty($user_info)) {
            $this->error('客户不存在或已被删除');
        } else {
            if ($user_info['parent_id']) {
                $this->error('该用户不是公海客户');
            }
            $user_info->delete_time = time();
            $res = $user_info->save();
            if ($res) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }


    }
}
