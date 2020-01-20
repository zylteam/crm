<?php


namespace app\admin\model;


use think\Model;

class ActivitySignModel extends Model
{
    public function activity()
    {
        return $this->hasOne('ActivityModel', 'id', 'activity_id');
    }

    public function user()
    {
        return $this->hasOne('UserModel','id','user_id')->bind('user_login');
    }
}
