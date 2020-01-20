<?php


namespace plugins\wechat\services;


use EasyWeChat\Foundation\Application;
use plugins\wechat\model\WechatReplyModel;
use think\Controller;

class WechatService extends Controller
{
    private static $instance = null;

    public static function options()
    {
        $options = SystemConfigService::get_web_config();
        return $options;
    }

    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }

    public static function serve()
    {
        $wechat = new Application(self::options());
        $server = $wechat->server;
        var_dump($server);
        die();
        self::hook($server);
        $response = $server->serve();
        return $response;
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