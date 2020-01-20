<?php


namespace plugins\wechat\controller;

use app\admin\model\WechatSettingModel;
use app\models\user\User;
use app\models\user\WechatUser;
use crmeb\repositories\MessageRepositories;
use crmeb\services\QrcodeService;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use plugins\wechat\model\WechatReplyModel;
use plugins\wechat\services\SystemConfigService;
use plugins\wechat\WechatPlugin;
use think\Controller;

class WechatController extends Controller
{
    public function index()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();

        $aa = SystemConfigService::get_web_config();
        var_dump($aa);
    }

    public function oauth_callback()
    {
        $id = input('id');
        $setting = WechatSettingModel::where('company_id', $id)->find();
        $plugin = new  WechatPlugin();
        $config = unserialize($setting['setting']);
        $options = [
            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id' => $config['appid'],         // AppID
            'secret' => $config['secret'],     // AppSecret
            'token' => $config['token'],          // Token
            'aes_key' => $config['EncodingAESKey'],                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！

            /**
             * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
             * 使用自定义类名时，构造函数将会接收一个 `EasyWeChat\Kernel\Http\Response` 实例
             */
            'response_type' => 'array',

            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * path：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'default' => 'dev', // 默认使用的 channel，生产环境可以改为下面的 prod
                'channels' => [
                    // 测试环境
                    'dev' => [
                        'driver' => 'single',
                        'path' => __DIR__ . '/logs/wechat.log',
                        'level' => 'debug',
                    ],
                    // 生产环境
                    'prod' => [
                        'driver' => 'daily',
                        'path' => __DIR__ . '/logs/wechat.log',
                        'level' => 'info',
                    ],
                ],
            ],

            /**
             * 接口请求相关配置，超时时间等，具体可用参数请参考：
             * http://docs.guzzlephp.org/en/stable/request-config.html
             *
             * - retries: 重试次数，默认 1，指定当 http 请求失败时重试的次数。
             * - retry_delay: 重试延迟间隔（单位：ms），默认 500
             * - log_template: 指定 HTTP 日志模板，请参考：https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php
             */
            'http' => [
                'max_retries' => 1,
                'retry_delay' => 500,
                'timeout' => 5.0,
                // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
            ],

            /**
             * OAuth 配置
             *
             * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
             * callback：OAuth授权完成后的回调页地址
             */
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/plugin/wechat/wechat/oauth_callback',
            ],
        ];
        $app = new Application($options);
        $server = $app->server;
        self::hook($server);
        $response = $server->serve();
        $response->send(); //微信回调设置
    }


    public function web_wechat_config()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();
        $web_config = [
            'app_id' => $config['web_appid'],
            'secret' => $config['web_secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/plugin/wechat/wechat/web_oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($web_config);
        $oauth = $app->oauth;
        $oauth->redirect()->send();
    }

    public function web_oauth_callback()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();
        $web_config = [
            'app_id' => $config['web_appid'],
            'secret' => $config['web_secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/plugin/wechat/wechat/web_oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($web_config);
        $oauth = $app->oauth;

        $user = $oauth->user()->toArray();
        $openid = $user['id'];
        header('Location:http://html.test2.zhicaisoft.cn?openid=' . $openid);
    }

    public static function hook($server)
    {
        $server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    switch (strtolower($message['Event'])) {
                        case 'subscribe':

                            $response = WechatReplyModel::reply('subscribe');
                            return json_encode($response);
                            if (isset($message['EventKey'])) {

                                if ($message['EventKey'] && ($qrInfo = QrcodeService::getQrcode($message['Ticket'], 'ticket'))) {
                                    QrcodeService::scanQrcode($message['Ticket'], 'ticket');
                                    if (strtolower($qrInfo['third_type']) == 'spread') {
                                        try {
                                            $spreadUid = $qrInfo['third_id'];
                                            $uid = WechatUser::openidToUid($message['FromUserName'], 'openid');
                                            if ($spreadUid == $uid) return '自己不能推荐自己';
                                            $userInfo = User::getUserInfo($uid);
                                            if ($userInfo['spread_uid']) return '已有推荐人!';
                                            if (!User::setSpreadUid($userInfo['uid'], $spreadUid)) {
                                                $response = '绑定推荐人失败!';
                                            }
                                        } catch (\Exception $e) {
                                            return 'aaa';
                                            $response = $e->getMessage();
                                        }
                                    }
                                }
                            }
                            break;
                        case 'unsubscribe':
                            event('WechatEventUnsubscribeBefore', [$message]);
                            break;
                        case 'scan':
                            $response = WechatReply::reply('subscribe');
                            if ($message->EventKey && ($qrInfo = QrcodeService::getQrcode($message->Ticket, 'ticket'))) {
                                QrcodeService::scanQrcode($message->Ticket, 'ticket');
                                if (strtolower($qrInfo['third_type']) == 'spread') {
                                    try {
                                        $spreadUid = $qrInfo['third_id'];
                                        $uid = WechatUser::openidToUid($message->FromUserName, 'openid');
                                        if ($spreadUid == $uid) return '自己不能推荐自己';
                                        $userInfo = User::getUserInfo($uid);
                                        if ($userInfo['spread_uid']) return '已有推荐人!';
                                        if (User::setSpreadUid($userInfo['uid'], $spreadUid)) {
                                            $response = '绑定推荐人失败!';
                                        }
                                    } catch (\Exception $e) {
                                        $response = $e->getMessage();
                                    }
                                }
                            }
                            break;
                        case 'location':
                            $response = MessageRepositories::wechatEventLocation($message);
                            break;
                        case 'click':
                            $response = WechatReply::reply($message->EventKey);
                            break;
                        case 'view':
                            $response = MessageRepositories::wechatEventView($message);
                            break;
                    }
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    $response = MessageRepositories::wechatMessageOther($message);
                    break;
            }
            return $response ? $response : false;
            // ...
        });
    }
}
