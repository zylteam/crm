<?php


namespace app\admin\model;


use think\Model;

class MobileAuthRuleModel extends Model
{
    public function roles()
    {
        return $this->belongsToMany('UserRoleModel', '\\app\\admin\\model\\MobileAuthAccess');
    }
}
