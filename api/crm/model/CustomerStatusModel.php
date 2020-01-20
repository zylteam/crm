<?php


namespace api\crm\model;


use think\Model;

class CustomerStatusModel extends Model
{
    public function customerList()
    {
        return $this->hasMany('UserModel', 'customer_status', 'id');
    }
}
