<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class UserModel extends Model
{

    protected $type = [
        'more' => 'array',
    ];

    public function relationShip()
    {
        return $this->belongsTo('UserRoleRelationshipModel', 'id', 'user_id');
    }

    public function recommendUserInfo()
    {
        return $this->hasOne('UserModel', 'id', 'parent_id')->selfRelation();
    }

    public function companyInfo()
    {
        return $this->belongsTo('CompanyModel', 'company_id');
    }

    public function getStatusAttr($value)
    {
        $status = [0 => '已审核', 1 => '未审核', 2 => '审核失败'];
        return $status[$value];
    }

    public function getIsFinishedAttr($value)
    {
        $status = [0 => '未完成', 1 => '已完成'];
        return $status[$value];
    }

    public function storeInfo()
    {
        return $this->belongsTo('StoreModel', 'store_id');
    }

    public function customerStatus()
    {
        return $this->belongsTo('CustomerStatusModel', 'customer_status')->bind([
            'customer_status_name' => 'name'
        ]);
    }

    /**
     * 操作人
     * @return \think\model\relation\BelongsTo
     */
    public function userInfo()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

}
