<?php


namespace api\crm\model;


use think\Model;

class CartModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('WechatUserModel', 'user_id');
    }

    public function goodsInfo()
    {
        return $this->belongsTo('GoodsModel', 'goods_id');
    }

    public function goodsSpecInfo()
    {
        return $this->belongsTo('GoodsSpecModel', 'spec_id');
    }

}
