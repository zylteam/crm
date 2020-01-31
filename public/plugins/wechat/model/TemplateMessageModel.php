<?php


namespace plugins\wechat\model;


use think\Model;

class TemplateMessageModel extends Model
{
    public function companyInfo()
    {
        return $this->belongsTo('app\admin\model\CompanyModel', 'company_id');
    }
}
