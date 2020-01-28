<?php

namespace plugins\aliyun;

use cmf\lib\Plugin;

class AliyunPlugin extends Plugin
{
    public $info = [
        'name' => 'Aliyun',
        'title' => '阿里云直播插件',
        'description' => '阿里云直播插件',
        'status' => 1,
        'author' => 'zyl',
        'version' => '1.1.1',
        'demo_url' => '',
        'author_url' => '',
    ];

    public $hasAdmin = 0; //插件是否有后台管理界面

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
}
