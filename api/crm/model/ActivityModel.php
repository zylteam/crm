<?php


namespace api\crm\model;


use think\Model;

class ActivityModel extends Model
{
    public function sign()
    {
        return $this->hasMany('ActivitySignModel', 'activity_id');
    }
}
