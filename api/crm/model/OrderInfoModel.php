<?php


namespace api\crm\model;


use think\Model;

class OrderInfoModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function orderGoodsList()
    {
        return $this->hasMany('OrderGoodsModel', 'order_id');
    }
}
