<?php


namespace api\crm\model;


use think\Model;

class UserModel extends Model
{
    protected $insert = ['user_type' => 2, 'user_status' => 1];

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id')->bind([
            'company_name' => 'name'
        ]);
    }

    public function relation()
    {
        return $this->belongsTo('UserRoleRelationshipModel', 'id', 'user_id')->bind([
            'role_name',
            'user_role_id'
        ]);
    }

    public function getStatusAttr($value)
    {
        $status = [0 => '已审核', 1 => '未审核', 2 => '审核失败'];
        return $status[$value];
    }

    public function recommendUserInfo()
    {
        return $this->hasOne('UserModel', 'id', 'parent_id')->selfRelation();
    }

    public function userList()
    {
        return $this->hasMany('UserModel', 'parent_id');
    }

    public function customerTotal()
    {
        return $this->hasMany('UserModel', 'parent_id');
    }

    public function customerFinished()
    {
        return $this->hasMany('UserModel', 'parent_id');
    }

    /**
     * 操作人
     * @return \think\model\relation\BelongsTo
     */
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function recommendUser()
    {
        return $this->belongsTo('UserModel', 'parent_id');
    }

    public function customerStatus()
    {
        return $this->belongsTo('CustomerStatusModel', 'customer_status')->bind([
            'customer_status_name' => 'name'
        ]);
    }

    public function connectLog()
    {
        return $this->hasMany('ConnectLogModel', 'customer_id');
    }



}
