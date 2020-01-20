<?php


namespace api\crm\controller;


use api\crm\model\UserModel;
use api\crm\model\UserRoleRelationshipModel;
use api\crm\model\UserTokenModel;
use cmf\controller\RestBaseController;
use think\Db;
use think\Validate;

class LoginController extends RestBaseController
{
    /**
     * 登陆
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $validate = new Validate([
                'user_login' => 'require',
                'password' => 'require',
            ]);
            $validate->message([
                'user_login.require' => '请输入登陆账号',
                'password.require' => '请输入登陆密码',
            ]);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $user_info = UserModel::with('company_info')
                ->field('user_login,true_name,company_id,user_status,user_pass,id,role_name,company_id')
                ->where('is_delete', 0)
                ->where('user_login', $data['user_login'])->find();
            if (empty($user_info)) {
                $this->error('用户不存在或账号被删除');
            } else {
                switch ($user_info['user_status']) {
                    case 0:
                        $this->error('您已被拉黑!');
                    case 2:
                        $this->error('账户还没有验证成功!');
                }
                if (!cmf_compare_password($data['password'], $user_info['user_pass'])) {
                    $this->error("密码不正确!");
                }
                $allowedDeviceTypes = $this->allowedDeviceTypes;

                if (empty($this->deviceType) && (empty($data['device_type']) || !in_array($data['device_type'], $this->allowedDeviceTypes))) {
                    $this->error("请求错误,未知设备!");
                } else if (!empty($data['device_type'])) {
                    $this->deviceType = $data['device_type'];
                }
                $user_token = UserTokenModel::where(['user_id' => $user_info['id'], 'device_type' => $this->deviceType])->find();
                if (isset($data['id']) && $data['id']) {
                    if ($user_info['company_id'] != $data['id']) {
                        $this->error('登录失败');
                    }
                } else {
                    $this->error('登录失败');
                }
                if (empty($user_token) || empty($user_token['token'])) {
                    cmf_generate_user_token($user_info['id'], $this->deviceType);
                    $user_token = UserTokenModel::where(['user_id' => $user_info['id'], 'device_type' => $this->deviceType])->find();
                }
                if ($user_token['token']) {
                    $role_ship = UserRoleRelationshipModel::where('user_id', $user_info['id'])->find();
                    $user_token->user_role_id = $role_ship ? $role_ship['user_role_id'] : 0;
                    $user_token->save();
                    $user_info->last_login_time = time();
                    $user_info->ip = get_client_ip();
                    $user_info->save();
                }
                $this->success("登录成功!", ['token' => $user_token['token'], 'user' => $user_info]);
            }
        }
    }

    /**
     * 退出登录
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function logout()
    {
        $userId = $this->getUserId();
        $user_info = UserModel::get($userId);
        Db::name('user_token')->where([
            'token' => $this->token,
            'user_id' => $userId,
            'device_type' => $this->deviceType
        ])->update(['token' => '']);

        $this->success("退出成功!", $user_info['company_id']);
    }
}
