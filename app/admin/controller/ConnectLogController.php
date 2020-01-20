<?php


namespace app\admin\controller;


use api\crm\model\ConnectLogModel;
use api\crm\model\IntentionTagModel;
use app\admin\model\RoleUserModel;
use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;

class ConnectLogController extends AdminBaseController
{
    public function logs()
    {
        $admin_id = cmf_get_current_admin_id();
        $role_id = RoleUserModel::where('user_id', $admin_id)->value('role_id');
        $role_id = $role_id ? $role_id : 1;
        $this->assign('role_id', $role_id);
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $user_info = UserModel::get($admin_id);
            $where[] = ['type', '=', 0];
            if ($user_info['company_id']) {
                $where[] = ['company_id', '=', $user_info['company_id']];
            }
            if (isset($data['duration']) && $data['duration']) {
                $where[] = ['create_time', 'between time', $data['duration']];
            }
            $num = 10;
            $page = isset($data['page']) && $data['page'] ? intval($data['page']) : 1;
            $list = ConnectLogModel::with(['user_info', 'customer_info', 'progress_status', 'customer_status', 'company_info'])
                ->where($where)
                ->order('create_time desc')
                ->paginate($num, false, ['page' => $page])
                ->each(function ($item) {
                    if ($item['tag_ids']) {
                        $temp_tag = [];
                        $array = explode(',', $item['tag_ids']);
                        foreach ($array as $value) {
                            $temp_tag[] = IntentionTagModel::get($value)['name'];
                        }
                        $item['tag_ids'] = $temp_tag;
                    }
                    if ($item['img_url']) {
                        $temp_array = [];
                        $array = explode(',', $item['img_url']);
                        foreach ($array as $value) {
                            $temp_array[] = $value;
                        }
                        $item['img_url'] = $temp_array;
                    }
                    if ($item['order_pic']) {
                        $temp_array = [];
                        $array = explode(',', $item['order_pic']);
                        foreach ($array as $value) {
                            $temp_array[] = $value;
                        }
                        $item['order_pic'] = $temp_array;
                    }
                    return $item;
                });
            $this->success('', '', $list);
        }
        return $this->fetch();
    }
}
