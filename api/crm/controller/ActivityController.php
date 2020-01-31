<?php


namespace api\crm\controller;


use api\crm\model\ActivityModel;
use api\crm\model\ActivitySignModel;
use api\crm\model\WechatUserModel;
use api\crm\model\WechatUserPointsLogModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\Exception;

class ActivityController extends RestBaseController
{
    public function get_activity_list()
    {
        $data = $this->request->param();
        $num = 10;
        $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
        $type = isset($data['type']) && $data['type'] ? $data['type'] : 1;

        $where[] = ['company_id', '=', $data['company_id']];
        $where[] = ['is_delete', '=', 0];
        $now = time();
        if ($type == 1) {
            $where[] = ['begin_time', '<=', $now];
            $where[] = ['end_time', '>=', $now];
        } else {
            $where[] = ['end_time', '<', $now];
        }
        $list = ActivityModel::where($where)
            ->order('is_hot desc,create_time desc')
            ->paginate($num, false, ['page' => $page])
            ->each(function ($item) {
                if ($item['cover_img']) {
                    $item['cover_img'] = $this->request->domain() . $item['cover_img'];
                }
                $item['begin_time'] = date('Y年m月d日', $item['begin_time']);
                $item['end_time'] = date('Y年m月d日', $item['end_time']);
                $item['sign_begin_time'] = date('Y年m月d日', $item['sign_begin_time']);
                $item['sign_end_time'] = date('Y年m月d日', $item['sign_end_time']);
                return $item;
            });
        $this->success('活动列表', $list);
    }

    public function get_activity_detail()
    {
        $data = $this->request->param();
        $openid = isset($data['openid']) && $data['openid'] ? $data['openid'] : 0;

        $info = ActivityModel::withCount(['sign' => function ($query) {
            $query->where('status', 1);
        }])->where('is_delete', 0)->get($data['id']);
        if (empty($info)) {
            $this->error('此活动不存在或已被删除');
        }
        if ($info['imgs']) {
            $temp_array = [];
            $array = explode(',', $info['imgs']);
            foreach ($array as $item) {
                $temp_array[] = $this->request->domain() . $item;
            }
            $info['imgs'] = $temp_array;
        }
        if ($openid) {
            $user_id = $this->getWechatUserId();
            $sign_info = ActivitySignModel::where([
                'user_id' => $user_id,
                'activity_id' => $data['id'],
                'status' => 1])->find();
            if ($sign_info) {
                $info['is_sign'] = true;
            } else {
                $info['is_sign'] = false;
            }
        } else {
            $info['is_sign'] = false;
        }
        $info['content'] = preg_replace_callback('/<[img|IMG].*?src=[\'| \"](?![http|https])(.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/', function ($r) {
            $str = $this->request->domain() . $r[1];
            return str_replace($r[1], $str, $r[0]);
        }, $info['content']);
        $this->success('获取活动详细', $info);
    }

    public function activity_sign()
    {
        try {
            Db::startTrans();
            $data = $this->request->param();
            $user_id = $this->getWechatUserId();
            $activity_id = isset($data['id']) && $data['id'] ? intval($data['id']) : 0;
            $activity_info = ActivityModel::withCount(['sign' => function ($query) {
                $query->where('status', 1);
            }])->whereBetweenTimeField('begin_time', 'end_time')
                ->get($activity_id);
            if (empty($activity_info)) {
                throw new Exception('活动不存在或已过期');
            }
            $is_sign = ActivitySignModel::where(['activity_id' => $activity_id, 'user_id' => $user_id, 'status' => 1])->find();
            if ($is_sign) {
                throw new Exception('请勿重复报名');
            }
            if ($activity_info['sign_count'] >= $activity_info['quota_num']) {
                throw new Exception('报名额度已满');
            }
            $now = time();
            if ($now > $activity_info['sign_end_time']) {
                throw new Exception('该活动报名时间已过');
            }
            if ($now < $activity_info['sign_begin_time']) {
                throw new Exception('该活动还未开始报名');
            }
            $user_info = WechatUserModel::get($user_id);
            if ($activity_info['money'] > 0) {
                $order_sn = 'ACTIVITY_' . cmf_get_order_sn();
                //报名费
                $params['company_id'] = $activity_info['company_id'];
                $params['url'] = $data['url'];
                $params['openid'] = $user_info['openid'];
                $params['money'] = intval($activity_info['money'] * 100);
                $params['body'] = '报名' . $activity_info['title'];
                $params['order_sn'] = $order_sn;
                $res = hook('wechat_web_pay_order', $params);

                $model = new ActivitySignModel([
                    'user_id' => $user_id,
                    'activity_id' => $activity_id,
                    'status' => 0,
                    'order_sn' => $order_sn,
                    'company_id' => $activity_info['company_id']
                ]);
                $model->save();
                Db::commit();
                $this->success('等待支付', $res);
            } else {
                //免费
                $model = new ActivitySignModel([
                    'user_id' => $user_id,
                    'activity_id' => $activity_id,
                    'status' => 1,
                    'points' => $activity_info['give_points'],
                    'company_id' => $activity_info['company_id']
                ]);
                if ($activity_info['give_points'] > 0) {
                    $log = new WechatUserPointsLogModel([
                        'user_id' => $user_id,
                        'change_points' => $activity_info['give_points'],
                        'before_points' => $user_info['points'],
                        'after_points' => $user_info['points'] + $activity_info['give_points'],
                        'remark' => '报名' . $activity_info['title'] . '获得' . $activity_info['give_points'] . '积分'
                    ]);
                    $log->save();
                    $user_info->points = ['inc', $activity_info['give_points']];
                    $user_info->save();
                }
                $model->save();
                Db::commit();
                $this->success('报名成功');
            }

        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

    public function get_my_activity()
    {
        $data = $this->request->param();
        $num = 10;
        $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
        $user_id = $this->getWechatUserId();
        $list = ActivitySignModel::with('activity_info')->where(['user_id' => $user_id, 'status' => 1])
            ->order('create_time desc')
            ->paginate($num, false, ['page' => $page])
            ->each(function ($item) {
                $item['activity_info']['begin_time'] = date('Y年m月d日', $item['activity_info']['begin_time']);
                $item['activity_info']['end_time'] = date('Y年m月d日', $item['activity_info']['end_time']);
                return $item;
            });
        $this->success('我参与的活动', $list);
    }
}
