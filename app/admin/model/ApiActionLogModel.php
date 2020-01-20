<?php


namespace app\admin\model;


use think\Model;

class ApiActionLogModel extends Model
{
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id')->bind([
            'admin_name' => 'true_name',
            'admin_role_name' => 'role_name',
        ]);
    }
}
