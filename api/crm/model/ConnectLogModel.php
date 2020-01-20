<?php


namespace api\crm\model;


use think\Model;

class ConnectLogModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id')->bind([
            'admin_name' => 'true_name',
            'admin_role_name' => 'role_name'
        ]);
    }

    public function customerInfo()
    {
        return $this->belongsTo('UserModel', 'customer_id');
    }

    public function progressStatus()
    {
        return $this->belongsTo('IntentionTagModel', 'progress_status')->bind([
            'progress_status_name' => 'name'
        ]);
    }

    public function customerStatus()
    {
        return $this->belongsTo('CustomerStatusModel', 'customer_status')->bind([
            'customer_status_name' => 'name'
        ]);
    }

    public function getStatusAttr($value)
    {
        $status = [0 => '审核中', 1 => '已通过', 2 => '审核失败'];
        return $status[$value];
    }

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id')->bind([
            'company_name' => 'name'
        ]);
    }

    public function recommendUser()
    {
        return $this->belongsTo('UserModel', 'parent_id')->bind([
            'recommend_user_name' => 'true_name'
        ]);
    }

//    public function getTypeAttr($value)
//    {
//        $status = [0 => '跟进记录', 1 => '用户审核记录'];
//        return $status[$value];
//    }

}
