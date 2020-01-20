<?php


namespace app\admin\model;


use think\Model;

class UserRoleRelationshipModel extends Model
{
    public function roleInfo()
    {
        return $this->belongsTo('UserRoleModel', 'user_role_id')->bind([
            'role_name' => 'name'
        ]);
    }
}
