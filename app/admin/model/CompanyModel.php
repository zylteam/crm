<?php


namespace app\admin\model;


use think\Model;

class CompanyModel extends Model
{
    public function userList()
    {
        return $this->hasMany('UserModel', 'company_id');
    }

    public function userRoleList()
    {
        return $this->hasMany('UserRoleModel', 'company_id');
    }
}
