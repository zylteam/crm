<?php


namespace api\crm\controller;


use api\crm\model\ConnectLogModel;
use api\crm\model\CustomerStatusModel;
use api\crm\model\IntentionTagModel;
use api\crm\model\UserModel;
use cmf\controller\RestBaseController;
use Overtrue\Socialite\User;
use plugins\wechat\model\TemplateMessageModel;
use think\Db;
use think\Exception;

class ConnectController extends RestBaseController
{
    public function add_connect_log()
    {
        if ($this->request->isPost()) {
            $user_id = $this->getUserId();
            Db::startTrans();
            try {
                $data = $this->request->param();
                $validate_result = $this->validate($data, 'ConnectLog');
                if ($validate_result !== true) {
                    throw new Exception($validate_result);
                } else {
                    $customer_info = UserModel::get($data['customer_id']);
                    if ($customer_info['role_name']) {
                        throw new Exception('该用户不是客户');
                    }
                    $log = ConnectLogModel::where(['customer_id' => $data['customer_id'], 'status' => 0, 'type' => 0])->find();
                    if ($log) {
                        throw  new Exception('该用户还有未审核的跟进记录');
                    }
                    if (isset($data['order_price']) && $data['order_price']) {
                        if (!is_numeric($data['order_price'])) {
                            throw new  Exception('请输入正确的订单价格');
                        }
                    }
                    $data['user_id'] = $user_id;
                    $data['company_id'] = $customer_info['company_id'];
                    $data['store_id'] = $customer_info['store_id'];
                    $connect_log = ConnectLogModel::create($data);
                    $res = $connect_log->save();
                    if ($res) {
                        $user_info = UserModel::get($user_id);
                        $template_message = TemplateMessageModel::where(['company_id' => $customer_info['company_id'], 'template_title' => '跟进记录审核通知', 'status' => 1])
                            ->find();
                        if ($template_message['template_id']) {
                            $tag_ids = $data['tag_ids'];
                            $temp_array = explode(',', $tag_ids);
                            $tag_status = [];
                            foreach ($temp_array as $item) {
                                $tag_status[] = IntentionTagModel::get($item)['name'];
                            }
                            $param = [
                                'company_id' => $customer_info['company_id'],
                                'template_id' => $template_message['template_id'],
                                'openid' => 'oRdnWwhhnQiNotHmpYCV7_S_KhLs',
                                'url' => 'http://crmhtml.test2.zhicaisoft.cn/views/recordsList.html?name=待审核跟进记录',
                                'data' => [
                                    'first' => '客户跟进记录的审核通知',
                                    'keyword1' => $customer_info['true_name'],
                                    'keyword2' => $customer_info['mobile'],
                                    'keyword3' => implode(',', $tag_status),
                                    'keyword4' => CustomerStatusModel::get($data['customer_status'])['name'],
                                    'keyword5' => $user_info['true_name'],
                                    'remark' => $data['remark']
                                ]
                            ];
                            hook('send_template_message', $param);

                        }

                        Db::commit();
                        $this->success('添加记录成功');
                    } else {
                        throw new  Exception('添加记录失败');
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
    }
}
