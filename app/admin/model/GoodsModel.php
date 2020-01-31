<?php


namespace app\admin\model;


use think\Model;

class GoodsModel extends Model
{

    public function goodsSpecs()
    {
        return $this->hasMany('GoodsSpecModel', 'goods_id');
    }

    public function companyInfo()
    {
        return $this->hasOne('CompanyModel', 'id', 'company_id');
    }
}
