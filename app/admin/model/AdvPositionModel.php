<?php


namespace app\admin\model;


use think\Model;

class AdvPositionModel extends Model
{
    public function advList()
    {
        return $this->hasMany('AdvModel', 'position_id');
    }
}
