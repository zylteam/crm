<?php


namespace api\crm\validate;


use think\Validate;

class ConnectLogValidate extends Validate
{
    protected $rule = [
        'progress_status' => 'require',
        'customer_status' => 'require',
        'address' => 'require',
        'customer_id' => 'require',
        'img_url' => 'require',
        'tag_ids' => 'require',

    ];

    protected $message = [
        'progress_status.require' => '请选择装修进度',
        'customer_status.require' => '请选择交互动作标签',
        'address.require' => '当前地址不能为空',
        'customer_id.require' => '客户id不能为空',
        'img_url.require' => '请上传照片凭证',
        'tag_ids.require' => '请选择交互标签不能',
    ];
}
