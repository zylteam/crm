<?php


namespace api\crm\model;


use think\Model;

class CartModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function goodsInfo()
    {
        return $this->belongsTo('GoodsModel', 'goods_id');
    }

    /**
     * 添加购物车
     * @param $data
     */
    public function addCart($data)
    {

    }

    /**
     * 购物车清空
     * @param $data
     */
    public function removeCart($data)
    {

    }
}
