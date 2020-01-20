<?php


namespace app\admin\model;


use think\Model;

class StoreModel extends Model
{

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id');
    }

    public function userList()
    {
        return $this->hasMany('UserModel', 'store_id');
    }
}
