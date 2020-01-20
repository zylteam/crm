<?php


namespace api\crm\controller;


use api\crm\model\ConnectLogModel;
use api\crm\model\UserModel;
use cmf\controller\RestBaseController;
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
