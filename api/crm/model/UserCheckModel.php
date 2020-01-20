<?php


namespace api\crm\model;


use think\Model;

class UserCheckModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function getStatusAttr($value)
    {
        $status = [1 => '等待审核', 0 => '已审核', 2 => '审核失败'];
        return $status[$value];
    }

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id');
    }
}
