<?php


namespace api\crm\controller;


use api\crm\model\ActivityModel;
use api\crm\model\UserModel;
use api\crm\model\WechatUserModel;
use app\admin\model\TempUserRelationModel;
use cmf\controller\RestBaseController;
use plugins\wechat\model\CacheModel;
use plugins\wechat\model\WechatMenusModel;

class WechatController extends RestBaseController
{
    public function menus()
    {
        $admin_id = cmf_get_current_admin_id();
        $admin_info = UserModel::get($admin_id);
        $result = WechatMenusModel::where('company_id', $admin_info['company_id'])->value('menu');
        $menus = json_decode($result);
        $menus = $menus ? $menus : '[]';
        $this->success('获取微信菜单', ['menu' => $menus, 'admin_id' => $admin_id]);
    }

    public function test()
    {
//        $info = ActivitySignModel::with(['user_info'])->withJoin('activity')->get(1);
        $info = UserModel::get(1);
        $data['title'] = 'test';
        $model = new ActivityModel();
        $res = $model->addActivity($data);
        $this->success('', $info);
    }

    public function get_jssdk()
    {
        $data = $this->request->param();
        $id = isset($data['id']) && $data['id'] ? $data['id'] : 0;
        $zt = isset($data['zt']) && $data['zt'] ? $data['zt'] : 0;
        $zp = isset($data['zp']) && $data['zp'] ? $data['zp'] : 0;
        $url = 'http://crmhtml.test2.zhicaisoft.cn/views/addRecord.html?id=' . $id . '&zt=' . $zt . '&zp=' . $zp;
        $js = hook('get_jssdk', $url);
        $this->success('js', $js);
    }

    public function get_address_by_location()
    {
        $data = $this->request->param();
        $mapx = isset($data['mapx']) && $data['mapx'] ? $data['mapx'] : 0;
        $mapy = isset($data['mapy']) && $data['mapy'] ? $data['mapy'] : 0;
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=" . $mapy . "," . $mapx . "&key=L6DBZ-ZIGYW-JKQRR-OXY2S-67UTT-75FPC&get_poi=1";
        $res = file_get_contents($url);
        $res = json_decode($res)->result->pois;
        if ($res) {
            $result['address'] = $res[0]->address;
        } else {
            $result['address'] = '未获取到地址';
        }
        $this->success('获取地址', $result);
    }

    public function get_user_info()
    {
        $data = $this->request->param();
        $params['company_id'] = $data['id'];
        $params['return_url'] = isset($data['return_url']) && $data['return_url'] ? $data['return_url'] : '';
        $params['user_id'] = isset($data['user_id']) && $data['user_id'] ? $data['user_id'] : 0;
        hook('wechat_config', $params);
        $this->success('aa');
    }

    public function oauth_callback()
    {
        $data = $this->request->param();
        $params['company_id'] = $data['id'];
        $params['return_url'] = isset($data['return_url']) && $data['return_url'] ? $data['return_url'] : '';
        $params['user_id'] = isset($data['user_id']) && $data['user_id'] ? $data['user_id'] : 0;
        $user = hook('wechat_user', $params);
        $openid = $user[0]['original']['openid'];
        if ($params['user_id']) {
            $model = UserModel::get($params['user_id']);
            if (empty($model['openid'])) {
                $model->openid = $openid;
                $model->save();
            }
        }
        $user_model = WechatUserModel::where('openid', $openid)->find();
        if (empty($user_model)) {
            $user_model = new WechatUserModel([
                'openid' => $user[0]['original']['openid'],
                'nickname' => $user[0]['original']['nickname'],
                'headimgurl' => $user[0]['original']['headimgurl'],
                'sex' => $user[0]['original']['sex'],
                'city' => $user[0]['original']['city'],
                'province' => $user[0]['original']['province'],
                'language' => $user[0]['original']['language'],
                'country' => $user[0]['original']['country'],
                'company_id' => $data['id'],
            ]);
        } else {
            $user_model->nickname = $user[0]['original']['nickname'];
            $user_model->headimgurl = $user[0]['original']['headimgurl'];
            $user_model->sex = $user[0]['original']['sex'];
            $user_model->city = $user[0]['original']['city'];
            $user_model->province = $user[0]['original']['province'];
            $user_model->language = $user[0]['original']['language'];
            $user_model->country = $user[0]['original']['country'];
        }
        $user_model->save();
        if ($params['return_url']) {
            $url = $params['return_url'] . '&openid=' . $openid;
        } else {
            $url = 'http://crmhtml.test2.zhicaisoft.cn/test.html?openid=' . $openid;
        }
        header('Location:' . $url);
    }

    public function send_message()
    {
        $params['company_id'] = 1;
        $params['openid'] = 'oRdnWwhhnQiNotHmpYCV7_S_KhLs';
        $params['template_id'] = '1SUA3v9FbuTox9C8a4L3W1KF_nonZ4Tmkf4TDuUg-aU';
        $params['url'] = 'http://www.baidu.com';
        $params['data'] = [
            'first' => '内容',
            'keyword1' => '11',
            'keyword2' => '222',
            'keyword3' => '333',
            'keyword4' => date('Y-m-d H:i:s', time()),
            'remark' => '测试'
        ];
        $res = hook('send_template_message', $params);
        $this->success('ok', $res);
    }
}
