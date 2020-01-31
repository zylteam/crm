<?php


namespace api\crm\model;


use think\Model;

class ActivitySignModel extends Model
{
    public function activityInfo()
    {
        return $this->belongsTo('ActivityModel', 'activity_id');
    }


}
