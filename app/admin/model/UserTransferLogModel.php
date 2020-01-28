<?php


namespace app\admin\model;


use think\Model;

//用户转移记录
class UserTransferLogModel extends Model
{
    public function fromUser()
    {
        $this->belongsTo('UserModel', 'from_user_id')->bind([
            'true_name' => 'from_user_true_name'
        ]);
    }

    public function toUser()
    {
        $this->belongsTo('UserModel', 'to_user_id')->bind([
            'true_name' => 'to_user_true_name'
        ]);
    }

    public function operatorUser()
    {
        $this->belongsTo('UserModel', 'user_id')->bind([
            'true_name' => 'operator_user_true_name'
        ]);
    }

    public function customerUser()
    {
        $this->belongsTo('UserModel', 'customer_id')->bind([
            'true_name' => 'customer_user_true_name'
        ]);
    }
}
