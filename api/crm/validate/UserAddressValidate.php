<?php


namespace api\crm\validate;


use think\Validate;

class UserAddressValidate extends Validate
{
    protected $rule = [
        'province' => 'require',
        'city' => 'require',
        'area' => 'require',
        'name' => 'require',
        'mobile' => 'require|mobile',
        'address' => 'require',
    ];

    protected $message = [
        'province.require' => '请选择省份',
        'city.require' => '请选择市区',
        'area.require' => '请选择区域',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '请输入有效的手机号码',
        'name.require' => '请输入联系人',
        'address.require' => '请输入详细地址信息',
    ];
}
