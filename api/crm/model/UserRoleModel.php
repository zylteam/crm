<?php


namespace api\crm\model;


use think\Model;

class UserRoleModel extends Model
{
    public function auths()
    {
        return $this->belongsToMany('MobileAuthRuleModel', '\\app\\admin\\model\\MobileAuthAccess', 'mobile_auth_rule_id', 'user_role_id');
    }
}
