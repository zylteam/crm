<?php


namespace app\admin\model;


use think\Model;

class ActivityModel extends Model
{
    public function signList()
    {
        return $this->hasMany('ActivitySignModel', 'activity_id');
    }
}
