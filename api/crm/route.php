<?php

use think\facade\Route;

Route::get('crm/menus', 'crm/wechat/menus');
Route::get('crm/test', 'crm/wechat/test');
//get_address_by_location
Route::get('crm/get_jssdk', 'crm/wechat/get_jssdk');
Route::get('crm/get_address_by_location', 'crm/wechat/get_address_by_location');
//登录接口
Route::post('login/login', 'crm/login/login');
//登出接口
Route::get('login/logout', 'crm/login/logout');
//获取用户菜单信息
Route::get('crm/get_menus', 'crm/menu/getUserMenu');
//获取客户经理列表接口
Route::get('crm/get_customer_manager_list', 'crm/user/get_customer_manager_list');
//获取客户来源
Route::get('crm/get_customer_source', 'crm/Other/get_customer_source');
//获取客户状态
Route::get('crm/get_customer_status', 'crm/Other/get_customer_status');
//获取进度标签
Route::get('crm/get_tag_list', 'crm/Other/get_tag_list');
//设计师页面 搜索 业务经理 和客户经理
Route::get('crm/get_manager_list', 'crm/Other/get_manager_list');
//获取公司信息 get_company_info
Route::get('crm/get_company_info', 'crm/Other/get_company_info');
//get_invite_message
Route::get('crm/get_invite_message', 'crm/Other/get_invite_message');
Route::group('crm', function () {
    Route::get('user_list', 'crm/User/user_list');
    //新增业务经理客户经理
    Route::post('add_service_manager', 'crm/User/add_service_manager');
    //更新业务经理客户经理
    Route::post('update_service_manager', 'crm/User/update_service_manager');
    //查找业务经理 客户经理列表
    Route::get('service_manager_list', 'crm/User/service_manager_list');
    //新增设计师
    Route::post('add_designer', 'crm/User/add_designer');
    //新增客户
    Route::post('add_user', 'crm/User/add_user');
    //设计师列表  designer_detail
    Route::get('designer_list', 'crm/User/designer_list');
    //查看设计师详细
    Route::get('designer_detail', 'crm/User/designer_detail');
    //获取待审核客户
    Route::get('check_list', 'crm/Customer/check_list');
    //获取已完成客户
    Route::get('finished_user', 'crm/Customer/finished_user');
    //获取待审核跟进记录
    Route::get('check_log_list', 'crm/Customer/check_log_list');
    //根据id获取未审核客户信息
    Route::get('get_check_user_info', 'crm/Customer/get_check_user_info');
    //审核客户接口
    Route::post('check', 'crm/Customer/check');
    //aatransfer_user
    Route::post('transfer_user', 'crm/Customer/transfer_user');
    //查找用户详细 search_user_log
    Route::get('search_user_detail', 'crm/Customer/search_user_detail');
    //公海客户
    Route::get('common_user', 'crm/Customer/common_user');
    //删除公海客户
    Route::get('delete_common_user', 'crm/Customer/delete_common_user');
    //审核跟进记录
    Route::post('check_record', 'crm/Customer/check_record');
    //释放客户到公海
    Route::post('release', 'crm/Customer/release');
    //查找用户记录
    Route::get('search_user_log', 'crm/Customer/search_user_log');
    //客户统计 customer_statistics
    Route::get('customer_statistics', 'crm/Customer/customer_statistics');
    //报表
    Route::get('report', 'crm/User/report');

    //添加跟进记录
    Route::post('add_record', 'crm/Connect/add_connect_log');
})->middleware(api\crm\http\middleware\Check::Class);

//上传图片接口
Route::post('crm/upload', 'crm/Upload/upload');
//获取微信用户信息 send_message
Route::get('crm/user_info', 'crm/Wechat/get_user_info');

Route::group('wechat', function () {
    Route::get('oauth_callback', 'crm/Wechat/oauth_callback');
    Route::get('send_message', 'crm/Wechat/send_message');
    Route::get('pay_order', 'crm/WechatUser/pay_order');
    Route::post('refund_order', 'crm/WechatUser/refund_order');
    Route::get('get_my_activity', 'crm/Activity/get_my_activity');
    Route::post('add_cart', 'crm/Goods/add_cart');
    Route::get('get_user_cart', 'crm/Goods/get_user_cart');
    Route::post('activity_sign', 'crm/Activity/activity_sign');//get_my_activity
    //新增地址 set_user_default_address get_user_address
    Route::post('add_user_address', 'crm/WechatUser/add_user_address');
    Route::post('set_user_default_address', 'crm/WechatUser/set_user_default_address');
    Route::post('delete_user_address','crm/WechatUser/delete_user_address');
    Route::post('get_user_address','crm/WechatUser/get_user_address');
});
//pay_order
//活动入口
Route::get('activity/get_activity_list', 'crm/Activity/get_activity_list');
Route::get('activity/get_activity_detail', 'crm/Activity/get_activity_detail');

//商品
Route::get('goods/get_goods_list', 'crm/Goods/get_goods_list');
Route::get('goods/get_goods_by_id', 'crm/Goods/get_goods_by_id');
//广告
Route::get('adv/get_adv', 'crm/Adv/get_adv');


