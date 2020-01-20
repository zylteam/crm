<?php


namespace api\crm\validate;


use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'user_login' => 'require|unique:user|mobile',
        'password' => 'require|length:6,10',
        'mobile' => 'require|mobile',
        'true_name' => 'require',
        'role_name' => 'require',
        'parent_id' => 'require',
        'customer_manager_id' => 'require',
        'img_url' => 'require',
        'card_no' => 'require',
    ];

    protected $message = [
        'user_login.require' => '手机号码不能为空',
        'user_login.unique' => '该手机号码已被使用',
        'user_login.mobile' => '请输入有效的手机号码',
        'password.require' => '密码不能为空',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '请输入有效的手机号码',
        'password.length' => '密码长度在6到10位',
        'true_name.require' => '姓名不能为空',
        'role_name.require' => '请选择用户角色',
        'parent_id.require' => '业务经理不能为空',
        'customer_manager_id.require' => '客户经理不能为空',
        'img_url.require' => '请上传照片凭证',
        'card_no.require' => '返卡账号不能为空',
    ];

    protected $scene = [
        //添加业务经理
        'service_manager' => ['user_login', 'password', 'true_name', 'role_name'],
        //添加客户经理
        'customer_manager' => ['user_login', 'password', 'true_name', 'role_name', 'parent_id'],
        //修改信息
        'edit' => ['user_login', 'true_name'],
        //设计师
        'designer' => ['user_login', 'true_name', 'role_name', 'company_name', 'card_no', 'img_url', 'password', 'customer_manager_id'],
        //客户
        'customer' => ['mobile', 'true_name', 'address', 'province', 'city', 'area', 'img_url', 'source'],
    ];
}
