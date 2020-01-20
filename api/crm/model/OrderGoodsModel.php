<?php


namespace api\crm\model;


use think\Model;

class OrderGoodsModel extends Model
{
    public function orderInfo()
    {
        return $this->belongsTo('OrderInfoModel', 'order_id');
    }
}
