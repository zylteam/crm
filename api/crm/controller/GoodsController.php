<?php


namespace api\crm\controller;


use api\crm\model\CartModel;
use api\crm\model\GoodsModel;
use api\crm\model\GoodsSpecModel;
use api\crm\model\OrderInfoModel;
use cmf\controller\RestBaseController;
use plugins\wechat\model\WechatUserModel;
use think\Db;
use think\Exception;

class GoodsController extends RestBaseController
{
    public function get_goods_list()
    {
        $data = $this->request->param();
        $num = 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $company_id = isset($data['company_id']) && $data['company_id'] ? $data['company_id'] : 0;
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['is_on_sale', '=', 1];
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

    public function add_cart()
    {
        try {
            Db::startTrans();
            $data = $this->request->param();
            $goods_id = isset($data['goods_id']) && $data['goods_id'] ? $data['goods_id'] : 0;
            $num = isset($data['num']) && $data['num'] ? intval($data['num']) : 0;
            $spec_id = isset($data['spec_id']) && $data['spec_id'] ? $data['spec_id'] : 0;
            $goods_info = GoodsModel::with('goods_specs')->where(['is_on_sale' => 1, 'is_delete' => 0])->get($goods_id);
            if (empty($goods_info)) {
                throw new Exception('商品不存在或已删除');
            }
            $spec_info = GoodsSpecModel::where(['goods_id' => $goods_id, 'id' => $spec_id])->find();
            if (empty($spec_info)) {
                if ($goods_info['stock'] <= 0) {
                    throw new Exception('商品库存不足');
                }

                if (count($goods_info['goods_specs']) > 0) {
                    throw new Exception('请选择规格');
                }
                $unit_price = $goods_info['price'];
            } else {
                if ($spec_info['stock'] <= 0) {
                    throw new Exception('商品库存不足');
                }
                $unit_price = $spec_info['price'];
            }
            if ($num <= 0) {
                throw new Exception('购买商品数不能为0');
            }
            $user_id = $this->getWechatUserId();
            $cart_model = CartModel::where(['goods_id' => $goods_id, 'user_id' => $user_id, 'status' => 0, 'spec_id' => $spec_id])->find();

            if (empty($cart_model)) {
                $cart_model = new  CartModel();
                $cart_model->goods_id = $goods_id;
                $cart_model->user_id = $user_id;

                $goods_info->stock = ['dec', $num];
                if ($spec_info) {
                    $spec_info->stock = ['dec', $num];
                    $spec_info->save();
                }
                $goods_info->save();
            } else {
                if ($cart_model['num'] > $num) {
                    $goods_info->stock = ['inc', $cart_model['num'] - $num];
                    if ($spec_info) {
                        $spec_info->stock = ['inc', $cart_model['num'] - $num];
                        $spec_info->save();
                    }
                    $goods_info->save();
                } else if ($cart_model['num'] < $num) {
                    $goods_info->stock = ['dec', $num - $cart_model['num']];
                    if ($spec_info) {
                        $spec_info->stock = ['dec', $num - $cart_model['num']];
                        $spec_info->save();
                    }
                    $goods_info->save();
                } else if ($cart_model['num'] == $num) {
                    throw new Exception('未增加购买数量');
                }
            }
            $cart_model->num = $num;
            $cart_model->unit_price = $unit_price;
            $cart_model->order_price = $unit_price * $num;
            if ($goods_info['buy_type'] >= 1) {
                $cart_model->points = $goods_info['points'];
            }
            $cart_model->spec_id = $spec_id;
            $res = $cart_model->save();
            if ($res) {
                Db::commit();
                $this->success('添加购物车成功');
            } else {
                throw new Exception('添加购物车失败');
            }
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    public function get_user_cart()
    {
        $user_id = $this->getWechatUserId();
        $list = CartModel::with('goods_info,goods_spec_info')
            ->order('create_time desc')
            ->where(['user_id' => $user_id, 'status' => 0])->all();
        $this->success('获取我的购物车', $list);
    }

    public function confirm_order()
    {
        try {
            Db::startTrans();
            $data = $this->request->param();
            $cart_ids = isset($data['cart_ids']) && $data['cart_ids'] ? $data['cart_ids'] : '';
            $goods_id = isset($data['goods_id']) && $data['goods_id'] ? $data['goods_id'] : '';
            $user_id = $this->getWechatUserId();
            $order_price = 0;
            $real_price = 0;
            $need_pay_points = 0;
            //购物车
            if ($cart_ids) {
                $cart_info = CartModel::where('id', 'in', $cart_ids)
                    ->where('status', 0)
                    ->all();
            }
            //直接购买
            if ($goods_id) {
                $goods_info = GoodsModel::with(['goods_specs'])->get($goods_id);
                $num = isset($data['num']) && $data['num'] ? $data['num'] : 1;
                $spec_id = isset($data['spec_id']) && $data['spec_id'] ? $data['spec_id'] : 0;
                if (count($goods_info['goods_specs']) > 0) {
                    $spec_info = GoodsSpecModel::get($spec_id);
                    if (empty($spec_info)) {
                        throw new Exception('请选择商品规格');
                    }
                }
                $real_price = $order_price = $goods_info['price'] * $num;
                if ($goods_info['buy_type'] >= 1) {
                    $need_pay_points = $goods_info['points'];
                }
            }
            $order_model = new  OrderInfoModel();
            $order_model->order_sn = 'GOODS_' . cmf_get_order_sn();
            $order_model->user_id = $user_id;
            $order_model->order_price = $order_price;
            $order_model->real_price = $real_price;
            $order_model->pay_points = $need_pay_points;
            $order_model->order_status = 0;


        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }
}
