<?php


namespace api\crm\controller;


use api\crm\model\AdvModel;
use cmf\controller\RestBaseController;

class AdvController extends RestBaseController
{
    public function get_adv()
    {
        $data = $this->request->param();
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $position_id = isset($data['position_id']) && $data['position_id'] ? $data['position_id'] : 0;
        $list = AdvModel::where(['company_id' => $company_id, 'position_id' => $position_id])
            ->order('sort desc')
            ->all()->each(function ($item) {
                if ($item['adv_img']) {
                    $item['adv_img'] = $this->request->domain() . $item['adv_img'];
                }
                return $item;
            });
        $this->success('获取广告信息', $list);
    }
}
