<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
return [
    'web_appid' => [
        'title' => '微信公众号appid',
        'type' => 'text',
        'value' => '',
        'tip' => '微信公众号appid'
    ],
    'web_secret' => [
        'title' => '微信公众号secret',
        'type' => 'text',
        'value' => '',
        'tip' => '微信公众号secret'
    ],
    'web_token' => [
        'title' => '微信验证token',
        'type' => 'text',
        'value' => '',
        'tip' => '微信公众号验证token',
    ],
    'radio'         => [
        'title'   => '消息加解密方式',
        'type'    => 'radio',
        'options' => [
            '1' => '明文模式', // 值=>显示
            '2' => '兼容模式',
            '3' => '安全模式',
        ],
        'value'   => '1',
        'tip'     => '如需使用安全模式请在管理中心修改，仅限服务号和认证订阅号',
    ],
    'EncodingAESKey' => [
        'title' => 'EncodingAESKey',
        'type' => 'text',
        'value' => '',
        'tip' => '公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅限服务号和认证订阅号',
    ],
    'server_address' => [
        'title' => '服务器地址',
        'type' => 'text',
        'value' => 'http://crm.test2.zhicaisoft.cn/plugin/wechat/wechat/oauth_callback',
        'disable'=>'true',
        'tip' => '服务器地址'
    ],
    'open_appid' => [
        'title' => '开发平台appid',
        'type' => 'text',
        'value' => '',
        'tip' => '开发平台appid'
    ],
    'open_secret' => [
        'title' => '开发平台secret',
        'type' => 'text',
        'value' => '',
        'tip' => '开发平台secret'
    ],
    'xcx_appid' => [
        'title' => '小程序appid',
        'type' => 'text',
        'value' => '',
        'tip' => '微信小程序appid'
    ],
    'xcx_secret' => [
        'title' => '小程序secret',
        'type' => 'text',
        'value' => '',
        'tip' => '微信小程序secret'
    ],
    'mch_id' => [
        'title' => '商户id',
        'type' => 'text',
        'value' => '',
        'tip' => '微信支付商户id'
    ],
    'key' => [
        'title' => 'api密钥',
        'type' => 'text',
        'value' => '',
        'tip' => '微信支付api密钥'
    ],
    'cert_path'=>[
        'title'=>'cert_path',
        'type'=>'text',
        'value'=>'',
        'tip'=>'绝对路径'
    ],
    'key_path'=>[
        'title'=>'key_path',
        'type'=>'text',
        'value'=>'',
        'tip'=>'绝对路径'
    ],
    'notify_url'=>[
        'title'=>'回调地址',
        'type'=>'text',
        'value'=>'',
        'tip'=>'订单回调地址'
    ],

];
