<?php

namespace plugins\wechat;

use app\admin\model\WechatSettingModel;
use cmf\lib\Plugin;
use EasyWeChat\Factory;

class WechatPlugin extends Plugin
{
    public $info = [
        'name' => 'Wechat', //Demo插件英文名，改成你的插件英文就行了
        'title' => '微信插件',
        'description' => '微信插件',
        'status' => 1,
        'author' => 'zyl',
        'version' => '1.1.1',
        'demo_url' => '',
        'author_url' => '',
    ];

    public $hasAdmin = 1; //插件是否有后台管理界面

    public function config()
    {
        $config = $this->getConfig();
        return $config;
    }

    // 插件安装
    public function install()
    {
        return true; //安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true; //卸载成功返回true，失败false
    }

    //实现的wechat_config钩子方法
    public function wechatConfig($param)
    {
        $company_id = $param['company_id'];
        $user_id = isset($param['user_id']) && $param['user_id'] ? $param['user_id'] : 0;
        $return_url = isset($param['return_url']) && $param['return_url'] ? $param['return_url'] : 0;
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id . '&user_id=' . $user_id . '&return_url=' . $return_url,
            ],
        ];
        $app = Factory::officialAccount($web_config);
        $oauth = $app->oauth;
        $oauth->redirect()->send();
    }

    public function wechatUser($param)
    {
        $company_id = $param['company_id'];
        $user_id = isset($param['user_id']) && $param['user_id'] ? $param['user_id'] : 0;
        $return_url = isset($param['return_url']) && $param['return_url'] ? $param['return_url'] : 0;
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id . '&user_id=' . $user_id . '&return_url=' . $return_url,

            ],
        ];
        $app = Factory::officialAccount($web_config);
        $oauth = $app->oauth;
        $user = $oauth->user();
        return $user->toArray();
    }

    public function sendTemplateMessage($param)
    {
        $company_id = $param['company_id'];
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id,

            ],
        ];
        $app = Factory::officialAccount($web_config);
        return $app->template_message->send([
            'touser' => $param['openid'],
            'template_id' => $param['template_id'], //1SUA3v9FbuTox9C8a4L3W1KF_nonZ4Tmkf4TDuUg-aU
            'url' => $param['url'],
            'data' => $param['data'],
        ]);
    }

    public function getJssdk($param)
    {
        $company_id = $param['company_id'];
        $user_id = isset($param['user_id']) && $param['user_id'] ? $param['user_id'] : 0;
        $return_url = isset($param['return_url']) && $param['return_url'] ? $param['return_url'] : 0;
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id . '&user_id=' . $user_id . '&return_url=' . $return_url,

            ],
        ];
        $app = Factory::officialAccount($web_config);
        $app->jssdk->setUrl($param['url']);
        $js = $app->jssdk->buildConfig(array('updateAppMessageShareData', 'getLocation'), $debug = false, $beta = true, $json = false);
        return $js;
    }

    public function getCouponColor($param)
    {
        $company_id = $param['company_id'];
        $user_id = isset($param['user_id']) && $param['user_id'] ? $param['user_id'] : 0;
        $return_url = isset($param['return_url']) && $param['return_url'] ? $param['return_url'] : 0;
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id . '&user_id=' . $user_id . '&return_url=' . $return_url,

            ],
        ];
        $app = Factory::officialAccount($web_config);
        $card = $app->card;
        $cardType = 'GROUPON';

        $card = $app->card;
//        $cardType = 'GROUPON';
//
//        $attributes = [
//            'base_info' => [
//                'logo_url' => 'http://mmbiz.qpic.cn/sz_mmbiz_jpg/ca2X3RtRt0Ysibydg1IRURQOxnHlmlKblZmSibH4EyVZAo7nmzzlE4GnlPUphFeNNQuiayrrEiaL69QRiaItOCI30ZQ/0',
//                'brand_name' => '方睿电器',
//                'code_type' => 'CODE_TYPE_TEXT',
//                'title' => '测试优惠券',
//                'color' => 'Color010',
//                'notice' => '卡券使用提醒',
//                'description' => '卡券描述',
//                "sku" => [
//                    "quantity" => 500000
//                ],
//                "date_info" => [
//                    "type" => "DATE_TYPE_FIX_TIME_RANGE",
//                    "begin_timestamp" => 1580054400,
//                    "end_timestamp" => 1581955200
//                ],
//            ],
//            'deal_detail' => '详细说明'
//        ];
//
//        $result = $card->create($cardType, $attributes);
//        $cards = [
//            'action_name' => 'QR_CARD',
//            'expire_seconds' => 1800,
//            'action_info' => [
//                'card' => [
//                    'card_id' => 'pRdnWwjhlEBXZ1NouwVShEURR0sw',
//                    'is_unique_code' => false,
//                    'outer_id' => 1,
//                ],
//            ],
//        ];
//
//        $result = $card->createQrCode($cards);
//        $url = $card->getQrCodeUrl($result['ticket']);
//        //查询卡券信息
        $result = $card->get('pRdnWwrIhs5Y40_05ZPexYGMwzmI');
        return $result;
    }

    public function getUserMemberCard($param)
    {
        $company_id = $param['company_id'];

        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',

        ];
        $app = Factory::officialAccount($web_config);
        $card = $app->card;
        $res = $card->member_card->getUser($param['openid'], $param['code']);
        return $res;
    }

    public function deleteCoupon($param)
    {
        $company_id = $param['company_id'];

        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',

        ];
        $app = Factory::officialAccount($web_config);
        $card = $app->card;
        $res = $card->delete($param['card_id']);
        return $res;
    }

    public function createCoupon($param)
    {
        $company_id = $param['company_id'];
        $user_id = isset($param['user_id']) && $param['user_id'] ? $param['user_id'] : 0;
        $return_url = isset($param['return_url']) && $param['return_url'] ? $param['return_url'] : 0;
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => '/api/wechat/oauth_callback/?id=' . $company_id . '&user_id=' . $user_id . '&return_url=' . $return_url,

            ],
        ];
        $app = Factory::officialAccount($web_config);
        $card = $app->card;
        $cardType = 'GROUPON';

        $attributes = [
            'base_info' => [
                'logo_url' => 'http://mmbiz.qpic.cn/sz_mmbiz_jpg/ca2X3RtRt0Ysibydg1IRURQOxnHlmlKblZmSibH4EyVZAo7nmzzlE4GnlPUphFeNNQuiayrrEiaL69QRiaItOCI30ZQ/0',
                'brand_name' => '方睿电器',
                'code_type' => 'CODE_TYPE_TEXT',
                'title' => '测试优惠券',
                'color' => 'Color010',
                'notice' => '卡券使用提醒',
                'description' => '卡券描述',
                "sku" => [
                    "quantity" => 500000
                ],
                "date_info" => [
                    "type" => "DATE_TYPE_FIX_TIME_RANGE",
                    "begin_timestamp" => 1580054400,
                    "end_timestamp" => 1581955200
                ],
            ],
        ];

//        $result = $card->create($cardType, $attributes);
        return $web_config;
    }

    /**
     * 上传临时素材
     * @param $param
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uploadMaterial($param)
    {
        $company_id = $param['company_id'];

        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',

        ];
        $app = Factory::officialAccount($web_config);
        $res = '';
        switch ($param['type']) {
            case 'img':
                $res = $app->material->uploadImage($param['path']);
                break;
            case 'voice';
                $res = $app->material->uploadVoice($param['path']);
                break;
            case 'video':
                $res = $app->material->uploadVideo($param['path']);
                break;
            case 'thumb':
                $res = $app->material->uploadThumb($param['path']);
                break;
        }
        return $res;
    }

    public function createMemberCard($param)
    {
        $company_id = $param['company_id'];

        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'response_type' => 'array',

        ];
        $app = Factory::officialAccount($web_config);
        $card = $app->card;
        $cardType = 'member_card';
        $attributes = [
            "background_pic_url" => $param['background_pic_url'],
            'prerogative' => $param['prerogative'],
            'auto_activate' => $param['auto_activate'] == 1 ? true : false,
            'supply_bonus' => $param['supply_bonus'] == 1 ? true : false,
            'wx_activate' => $param['wx_activate'] == 1 ? true : false,
            'supply_balance' => $param['supply_balance'] == 1 ? true : false,
            'base_info' => [
                'logo_url' => $param['logo_url'],
                'brand_name' => $param['brand_name'],
                'code_type' => $param['code_type'],
                'title' => $param['title'],
                'color' => $param['color'],
                'notice' => $param['notice'],
                'description' => $param['description'],
                "sku" => [
                    "quantity" => $param['quantity']
                ],
                'service_phone' => $param['service_phone'],

            ],
        ];
        if ($param['wx_activate'] == 0) {
            $attributes['activate_url'] = $param['activate_url'];
        }
        if ($param['supply_bonus'] == 0) {
            $attributes['bonus_url'] = $param['bonus_url'];
        }
        if ($param['supply_balance'] == 0) {
            $attributes['balance_url'] = $param['balance_url'];
        }
        $date_info = [];
        switch ($param['date_info_type']) {
            case 'DATE_TYPE_FIX_TIME_RANGE':
                $date_info['type'] = 'DATE_TYPE_PERMANENT';
                $date_info['begin_timestamp'] = $param['begin_timestamp'];
                $date_info['end_timestamp'] = $param['end_timestamp'];
                break;
            case 'DATE_TYPE_FIX_TERM':
                $date_info['type'] = 'DATE_TYPE_FIX_TERM';
                $date_info['fixed_term'] = $param['fixed_term'];
                $date_info['fixed_begin_term'] = $param['fixed_begin _term'];
                break;
            default:
                $date_info['type'] = 'DATE_TYPE_PERMANENT';
                break;
        }
        $attributes['base_info']['date_info'] = $date_info;
        if (isset($param['card_id']) && $param['card_id']) {
            $res = $card->update($param['card_id'], $cardType, $attributes);
        } else {
            $res = $card->create($cardType, $attributes);
        }

        return $res;
    }


    public function wechatWebPayOrder($param)
    {
        $company_id = $param['company_id'];
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],
            'cert_path' => __DIR__ . '/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($web_config);
        $jssdk = $app->jssdk;
        $result = $app->order->unify([
            'body' => $param['body'],
            'out_trade_no' => $param['order_sn'],
            'total_fee' => $param['money'],
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $param['openid'],
        ]);
        $url = $param['url'];
        $app->jssdk->setUrl($url);
        $js = $app->jssdk->buildConfig(array('chooseWXpay'), $debug = false, $beta = true, $json = false);
        $pay_config = $jssdk->bridgeConfig($result['prepay_id'], false);
        $pay_config['js'] = $js;
        return $pay_config;
    }


    public function wechatWebRefund($param)
    {
        $company_id = $param['company_id'];
        $setting = WechatSettingModel::where('company_id', $company_id)->find();
        $config = unserialize($setting['setting']);
        $web_config = [
            'app_id' => $config['appid'],
            'secret' => $config['secret'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],
            'cert_path' => __DIR__ . '/cert/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/cert/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($web_config);
        $order_sn = 'REFUND_' . cmf_get_order_sn();
        $result = $app->refund->byOutTradeNumber($param['order_sn'], $order_sn, $param['total_money'], $param['refund_money'], [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => $param['desc'],
        ]);
        return $result;
    }

    //实现的wechat_xcx_pay_order钩子方法
    public function wechatXcxPayOrder($param)
    {
        $config = $this->getConfig();
        $payment = [
            'app_id' => $config['xcx_appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($payment);
        $jssdk = $app->jssdk;
        $result = $app->order->unify([
            'body' => $param['body'],
            'out_trade_no' => $param['pay_sn'],
            'total_fee' => $param['pay_amount'] * 100,
            //'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            //'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $param['openid'],
        ]);
        $config = $jssdk->bridgeConfig($result['prepay_id'], false);
        return $config;
    }

    //实现的wechat_xcx_refund_order钩子方法
    public function wechatXcxRefundOrder($param)
    {
        //小程序退款
        $config = wechatConfig();
        $app = Factory::payment($config);
        $result = $app->refund->byOutTradeNumber($param['pay_sn'], $param['refund_sn'], $param['total_fee'], $param['refund_fee'], [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '退款',
        ]);
        return $result;
    }

    //实现的wechat_auth_login钩子方法
    public function wechatAuthLogin()
    {
        $config = $this->getConfig();
        $wechat_config = [
            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id' => $config['web_appid'],         // AppID
            'secret' => $config['web_secret'],     // AppSecret
            'token' => 'omJNpZEhZeHj1B5VFbk1HPZxFECKkP48',          // Token
            'aes_key' => 'WbWcudJ4JshmJGyRUIr61mfr2CmN0em0WnW9avFxrtG',                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！

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
                'callback' => '/user/login/oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($wechat_config);
        return $app;
    }

    public function wechatSearchOrder($param)
    {
        $config = $this->getConfig();
        $payment = [
            'app_id' => $config['web_appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($payment);
        $res = $app->transfer->queryBalanceOrder($param['order_sn']);
        return $res;
    }

    //实现的wechat_enterprise_transfer钩子方法
    public function wechatEnterpriseTransfer($param)
    {
        $config = $this->getConfig();
        $payment = [
            'app_id' => $config['web_appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($payment);
        $res = $app->transfer->toBalance([
            'partner_trade_no' => $param['order_sn'], // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $param['openid'],
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $param['money'] * 100, // 企业付款金额，单位为分
            'desc' => '商户结算', // 企业付款操作说明信息。必填
        ]);
        return $res;
    }

    public function wechatRedBag($param)
    {
        $config = $this->getConfig();
        $payment = [
            'app_id' => $config['web_appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => __DIR__ . '/1546962141_20190803_cert/' . $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
            'log' => [
                'level' => 'debug',
                'file' => __DIR__ . '/logs/wechat.log',
            ],
        ];
        $app = Factory::payment($payment);
        $redpack = $app->redpack;
        $redpackData = [
            'mch_billno' => $param['order_sn'],
            'send_name' => '测试红包',
            're_openid' => $param['openid'],
            'total_num' => 1,  //固定为1，可不传
            'total_amount' => $param['money'] * 100,  //单位为分，不小于100
            'wishing' => '祝福语',
            'client_ip' => '',  //可不传，不传则由 SDK 取当前客户端 IP
            'act_name' => '测试活动',
            'remark' => '测试备注',
            // ...
        ];
        $result = $redpack->sendNormal($redpackData);
        return $result;

    }
}
