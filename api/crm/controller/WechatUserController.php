<?php


namespace api\crm\controller;


use api\crm\model\WechatUserAddressModel;
use api\crm\model\WechatUserModel;
use app\admin\logic\HookLogic;
use cmf\controller\RestBaseController;

class WechatUserController extends RestBaseController
{

    public function add_user_address()
    {
        $user_id = $this->getWechatUserId();
        $data = $this->request->param();
        $validate_result = $this->validate($data, 'UserAddress');
        if ($validate_result !== true) {
            $this->error($validate_result);
        } else {
            $data['user_id'] = $user_id;
            $is_default = isset($data['is_default']) && $data['is_default'] ? $data['is_default'] : 0;
            if ($is_default == 1) {
                $list = WechatUserAddressModel::where('user_id', $user_id)->all();
                if ($list) {
                    WechatUserAddressModel::where('user_id', $user_id)->update(['is_default' => 0]);
                }
            }
            $model = WechatUserAddressModel::create($data);
            $res = $model->save();
            if ($res) {
                $this->success('新增成功');
            } else {
                $this->error('新增失败');
            }
        }
    }

    public function set_user_default_address()
    {
        $user_id = $this->getWechatUserId();
        $data = $this->request->param();
        $id = isset($data['id']) && $data['id'] ? $data['id'] : 1;
        $model = WechatUserAddressModel::where(['id' => $id, 'user_id' => $user_id])->find();
        if ($model) {
            $list = WechatUserAddressModel::where('user_id', $user_id)->all();
            if ($list) {
                WechatUserAddressModel::where('user_id', $user_id)->update(['is_default' => 0]);
            }
            $model->is_default = 1;
            $res = $model->save();
            if ($res) {
                $this->success('设置成功');
            } else {
                $this->error('设置失败');
            }
        } else {
            $this->error('数据异常');
        }
    }

    public function delete_user_address()
    {
        $user_id = $this->getWechatUserId();
        $data = $this->request->param();
        $id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
        $info = WechatUserAddressModel::get($id);
        if ($info) {
            $res = $info->delete();
            if ($res) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('地址不存在或已被删除');
        }
    }

    public function get_wechat_user_info()
    {
        $user_id = $this->getWechatUserId();
        $user_info = WechatUserModel::get($user_id);
        $this->success('获取微信用户信息', $user_info);
    }

    public function pay_order()
    {
        $params['company_id'] = 1;
        $res = hook('wechat_web_pay_order', $params);
        $this->success('', $res);
    }

    public function refund_order()
    {
        $data = $this->request->param();

        $param['order_sn'] = $data['order_sn'];
        $param['total_money'] = $data['money'];
        $param['refund_money'] = $data['money'];
        $param['company_id'] = $data['id'];
        $param['desc'] = '活动退款';
        $res = hook('wechat_web_refund', $param);
        $this->success('退款成功', $res);
    }
}
