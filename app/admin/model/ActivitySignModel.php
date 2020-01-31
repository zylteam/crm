<?php


namespace app\admin\model;


use think\Model;

class ActivitySignModel extends Model
{
    public function activityInfo()
    {
        return $this->hasOne('ActivityModel', 'id', 'activity_id');
    }

    public function userInfo()
    {
        return $this->hasOne('WechatUserModel', 'id', 'user_id');
    }

    public function getStatusAttr($value)
    {
        $status = [0 => '报名未支付', 1 => '报名成功', 2 => '取消报名', 3 => '支付失败'];
        return $status[$value];
    }
}
