<?php


namespace api\crm\controller;


use api\crm\model\ActivityModel;
use api\crm\model\WechatUserModel;
use cmf\controller\RestBaseController;

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
        $info = ActivityModel::where('is_delete', 0)->get($data['id']);
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
        $info['content'] = preg_replace_callback('/<[img|IMG].*?src=[\'| \"](?![http|https])(.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/', function ($r) {
            $str = $this->request->domain() . $r[1];
            return str_replace($r[1], $str, $r[0]);
        }, $info['content']);
        $this->success('获取活动详细', $info);
    }

    public function activity_sign()
    {
        $data = $this->request->param();
        $user_id = $this->getWechatUserId();
        $user_info = WechatUserModel::get($user_id);
        $this->success('', $user_info);
    }
}
