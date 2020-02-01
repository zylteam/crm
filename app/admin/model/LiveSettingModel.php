<?php


namespace app\admin\model;


use think\Model;

class LiveSettingModel extends Model
{
    public function companyInfo()
    {
        return $this->hasOne('CompanyModel', 'id', 'company_id');
    }
}
