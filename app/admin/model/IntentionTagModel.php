<?php


namespace app\admin\model;


use think\Model;

class IntentionTagModel extends Model
{
    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id')->bind(['company_name' => 'name']);
    }
}
