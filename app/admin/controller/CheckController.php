<?php


namespace app\admin\controller;


use api\crm\model\ConnectLogModel;
use api\crm\model\IntentionTagModel;
use api\crm\model\UserCheckModel;
use app\admin\model\ApiActionLogModel;
use app\admin\model\MobileAuthRuleModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class CheckController extends AdminBaseController
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $admin_id = cmf_get_current_admin_id();
            $admin_info = UserModel::get($admin_id);
            $where = [];
            if ($admin_info['company_id']) {
                $ids = UserModel::where('company_id', $admin_info['company_id'])->column('id');
                $where[] = ['user_id', 'in', $ids];
            }
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', $data['duration']];
            }
            $num = 10;
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $check_type = ['api/crm/check_record', 'api/crm/check', 'api/crm/delete_common_user'];
            $list = ApiActionLogModel::with(['user_info'])->where('role_rule', 'in', $check_type)
                ->order('create_time desc')
                ->where($where)
                ->paginate($num, false, ['page' => $page])
                ->each(function ($item) {
                    $success = '通过';
                    $fail = '失败';
                    if (!empty($item['post_data'])) {
                        $postData = json_decode($item['post_data']);
                        $item['data_id'] = $postData->id;
                        $status = isset($postData->status) ? $postData->status : 0;
                        if ($item['role_rule'] == 'api/crm/check') {
                            $check_user_info = UserCheckModel::with('user_info')->get($item['data_id']);

                            if ($check_user_info['img_url']) {
                                $temp_array = [];
                                $array = explode(',', $check_user_info['img_url']);
                                foreach ($array as $value) {
                                    $temp_array[] = $value;
                                }
                                $check_user_info['img_url'] = $temp_array;
                            }
                            if ($check_user_info['source'] == '设计师来源') {
                                $designer = UserModel::get($check_user_info['extend_field']);
                                $check_user_info['extend_field'] = $designer['true_name'];
                            }
                            $item['check_user_info'] = $check_user_info;
                            if ($status == 0) {
                                $item['status'] = $success;
                            } else {
                                $item['status'] = $fail;
                            }
                        } else if ($item['role_rule'] == 'api/crm/check_record') {
                            $log = ConnectLogModel::with(['user_info', 'customer_info', 'progress_status', 'customer_status', 'company_info'])
                                ->get($item['data_id']);
                            if ($log['tag_ids']) {
                                $temp_tag = [];
                                $array = explode(',', $log['tag_ids']);
                                foreach ($array as $value) {
                                    $temp_tag[] = IntentionTagModel::get($value)['name'];
                                }
                                $log['tag_ids'] = $temp_tag;
                            }
                            if ($log['img_url']) {
                                $temp_array = [];
                                $array = explode(',', $log['img_url']);
                                foreach ($array as $value) {
                                    $temp_array[] = $value;
                                }
                                $log['img_url'] = $temp_array;
                            }
                            if ($log['order_pic']) {
                                $temp_array = [];
                                $array = explode(',', $log['order_pic']);
                                foreach ($array as $value) {
                                    $temp_array[] = $value;
                                }
                                $log['order_pic'] = $temp_array;
                            }
                            $item['connect_log'] = $log;
                            if ($status == 1) {
                                $item['status'] = $success;
                            } else {
                                $item['status'] = $fail;
                            }
                        } else if ($item['role_rule'] == 'api/crm/delete_common_user') {
                            $check_user_info = UserModel::with('user_info')->get($item['data_id']);
                            if ($check_user_info['img_url']) {
                                $temp_array = [];
                                $array = explode(',', $check_user_info['img_url']);
                                foreach ($array as $value) {
                                    $temp_array[] = $value;
                                }
                                $check_user_info['img_url'] = $temp_array;
                            }
                            if ($check_user_info['source'] == '设计师来源') {
                                $designer = UserModel::get($check_user_info['extend_field']);
                                $check_user_info['extend_field'] = $designer['true_name'];
                            }
                            $item['check_user_info'] = $check_user_info;
                        }
                    }
                    $auth_rule = MobileAuthRuleModel::where('api_url', $item['role_rule'])->find();
                    $item['type'] = $auth_rule['name'];
                    return $item;
                });
            $this->success('', '', $list);
        }
        return $this->fetch();
    }
}
