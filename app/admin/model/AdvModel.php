<?php


namespace app\admin\model;


use think\Model;

class AdvModel extends Model
{
    public function position()
    {
        return $this->belongsTo('AdvPositionModel','position_id');
    }
}
