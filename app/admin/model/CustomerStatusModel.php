<?php


namespace app\admin\model;


use think\Model;

class CustomerStatusModel extends Model
{
    public function tagList()
    {
        return $this->hasMany('IntentionTagModel', 'status_id');
    }

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id')->bind(['company_name' => 'name']);
    }

}
