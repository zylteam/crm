<?php

namespace api\crm\http\middleware;

use api\crm\model\ApiActionLogModel;
use api\crm\model\UserModel;
use api\crm\model\UserRoleModel;
use api\crm\model\UserTokenModel;
use think\Exception;

class Check
{
    public function handle($request, \Closure $next)
    {
        try {
            $token = trim($request->header('XX-Token'));
            $device_type = trim($request->header('XX-Device-Type'));
            if ($token && $device_type) {
                $user_token = UserTokenModel::where(['token' => $token, 'device_type' => $device_type])->find();
                if (empty($user_token)) {
                    throw  new  Exception('用户未登录');
                } else {
                    if ($user_token['user_role_id'] == 0) {
                        throw  new  Exception('您无权限操作此功能');
                    }
                    $current_route = $request->routeInfo();
                    $rule = 'api/' . $current_route['rule'];
                    $user_role = UserRoleModel::get($user_token['user_role_id']);
                    $menu = $user_role->auths->where('api_url', $rule);
                    if (count($menu) == 0) {
                        throw new Exception('您无权限操作此功能');
                    }
                    $action_log = new ApiActionLogModel([
                        'user_id' => $user_token['user_id'],
                        'post_data' => json_encode($request->param()),
                        'role_rule' => $rule
                    ]);
                    $action_log->save();
                    return $next($request);
                }
            } else {
                throw  new  Exception('用户未登录');
            }

            return json($result);
        } catch (Exception $e) {
            $result = array('code' => 0, 'msg' => $e->getMessage());
            return json($result);
        }

    }
}
