<?php


namespace api\crm\model;


use think\Model;

class GoodsModel extends Model
{

    public function goodsSpecs()
    {
        return $this->hasMany('GoodsSpecModel', 'goods_id');
    }
}
