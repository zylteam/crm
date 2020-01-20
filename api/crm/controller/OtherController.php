<?php


namespace api\crm\controller;


use api\crm\model\CompanyModel;
use api\crm\model\UserModel;
use app\admin\model\CustomerSourceSettingModel;
use app\admin\model\CustomerStatusModel;
use app\admin\model\IntentionTagModel;
use app\admin\model\OtherModel;
use cmf\controller\RestBaseController;

class OtherController extends RestBaseController
{
    public function get_customer_source()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $list = CustomerSourceSettingModel::where('company_id', $user_info['company_id'])->all();
        $this->success('获取客户来源', $list);
    }

    public function get_customer_status()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $data = $this->request->param();
        //传入当前用户id
        $id = isset($data['is_finished']) && $data['is_finished'] ? $data['is_finished'] : 0;
        $where[] = ['type', '=', $id];
        $list = CustomerStatusModel::with('tag_list')
            ->where('company_id', $user_info['company_id'])
            ->where($where)
            ->order('sort asc')
            ->all();
        $this->success('获取客户状态', $list);
    }

    public function get_tag_list()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $list = IntentionTagModel::where(['company_id' => $user_info['company_id'], 'type' => 1])
            ->all();
        $this->success('获取客户状态', $list);
    }

    //设计师页面 搜索 业务经理 和客户经理
    public function get_manager_list()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $where[] = ['company_id', '=', $user_info['company_id']];
        $where[] = ['store_id', '=', $user_info['store_id']];
        $where[] = ['is_delete', '=', 0];
        if ($user_info['role_name'] == '店长') {
            $where[] = ['role_name', 'in', ['客户经理', '业务经理']];
        } else if ($user_info['role_name'] == '业务经理') {
            $where[] = ['role_name', 'in', ['客户经理']];
            $where[] = ['parent_id', '=', $user_id];
        } else if ($user_info['role_name'] == '客户经理') {
            $where[] = ['id', '=', $user_id];
        }
        $user_list = UserModel::field('id,true_name,role_name')
            ->order('create_time desc')->where($where)->all()->each(function ($item) {
                $item['name'] = $item['true_name'] . '(' . $item['role_name'] . ')';
                return $item;
            });
        $this->success('获取业务经理和客户经理', $user_list);
    }

    public function get_invite_message()
    {
        $user_id = $this->getUserId();
        $user_info = UserModel::get($user_id);
        $query = OtherModel::where(['company_id' => $user_info['company_id']])->find();
        $res = unserialize($query['content'])['message'];
        $this->success('邀请注册', $res);
    }

    public function get_company_info()
    {
        $id = input('id');
        $company_info = CompanyModel::get($id);
        $this->success('获取公司信息', $company_info);
    }
}
