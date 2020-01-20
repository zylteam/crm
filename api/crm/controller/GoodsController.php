<?php


namespace api\crm\controller;


use api\crm\model\GoodsModel;
use cmf\controller\RestBaseController;

class GoodsController extends RestBaseController
{
    public function get_goods_list()
    {
        $data = $this->request->param();
        $num = 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['is_on_sale', '=', 0];
        $where[] = ['is_delete', '=', 0];

        $goods_list = GoodsModel::where($where)
            ->order('sort desc,create_time desc')
            ->paginate($num, false, ['page' => $page])->each(function ($item) {
                if ($item['goods_img']) {
                    $array = explode(',', $item['goods_img']);
                    $temp_array = [];
                    foreach ($array as $value) {
                        $temp_array[] = $this->request->domain() . $value;
                    }
                    $item['goods_img'] = $temp_array;
                }
                return $item;
            });
        $this->success('获取商品列表', $goods_list);
    }

    public function get_goods_by_id()
    {
        $data = $this->request->param();
        $id = isset($data['id']) && $data['id'] ? intval($data['id']) : 0;
        $goods_info = GoodsModel::with('goods_specs')->where(['id' => $id, 'is_delete' => 0, 'is_on_sale' => 1])->find();
        if (empty($goods_info)) {
            $this->error('商品不存在或已删除');
        }
        if ($goods_info['goods_img']) {
            $array = explode(',', $goods_info['goods_img']);
            $temp_array = [];
            foreach ($array as $value) {
                $temp_array[] = $this->request->domain() . $value;
            }
            $goods_info['goods_detail'] = preg_replace_callback('/<[img|IMG].*?src=[\'| \"](?![http|https])(.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/', function ($r) {
                $str = $this->request->domain() . $r[1];
                return str_replace($r[1], $str, $r[0]);
            }, $goods_info['goods_detail']);
            $goods_info['goods_img'] = $temp_array;
        }
        $this->success('获取商品详细', $goods_info);
    }
}
