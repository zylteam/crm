<?php


namespace plugins\wechat\services;


use plugins\wechat\WechatPlugin;

class SystemConfigService
{
    public static function get_web_config()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();
        $options = [
            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id' => $config['web_appid'],         // AppID
            'secret' => $config['web_secret'],     // AppSecret
            'token' => $config['web_token'],          // Token
//            'aes_key' => $config['EncodingAESKey'],                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！

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
        if ($config['radio'] > 1 && isset($config['EncodingAESKey']) && $config['EncodingAESKey']) {
            $options['aes_key'] = $config['EncodingAESKey'];
        }
        if ($config['key'] && $config['cert_path'] && $config['key_path'] && $config['notify_url']) {
            $options['payment'] = [
                'app_id' => $config['web_appid'],         // AppID
                'secret' => $config['web_secret'],     // AppSecret
                'key' => $config['key'],
                'cert_path' => $config['cert_path'], // XXX: 绝对路径！！！！
                'key_path' => $config['key_path'],      // XXX: 绝对路径！！！！
                'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
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
            ];
        }
        return $options;
    }

    public static function get_web_payment()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();
        $payment = [
            'app_id' => $config['web_appid'],         // AppID
            'secret' => $config['web_secret'],     // AppSecret
            'key' => $config['key'],
            'cert_path' => $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
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
        ];
        return $payment;
    }

    public static function get_mini_config()
    {
        $plugin = new  WechatPlugin();
        $config = $plugin->config();
        $payment = [
            'app_id' => $config['xcx_appid'],         // AppID
            'secret' => $config['xcx_secret'],     // AppSecret
            'response_type' => 'array',
            'key' => $config['key'],
            'cert_path' => $config['cert_path'], // XXX: 绝对路径！！！！
            'key_path' => $config['key_path'],      // XXX: 绝对路径！！！！
            'notify_url' => $config['notify_url'],     // 你也可以在下单时单独设置来想覆盖它
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
        ];
        return $payment;
    }

}