<?php

use api\crm\model\WechatUserTokenModel;

function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
    $s1 = iconv('UTF-8', 'gb2312', $str);
    $s2 = iconv('gb2312', 'UTF-8', $s1);
    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return 'A';
}

function getUserListByAliasName($list)
{
    $char_array = [
        ['key' => 'A', 'user_list' => []],
        ['key' => 'B', 'user_list' => []],
        ['key' => 'C', 'user_list' => []],
        ['key' => 'D', 'user_list' => []],
        ['key' => 'E', 'user_list' => []],
        ['key' => 'F', 'user_list' => []],
        ['key' => 'G', 'user_list' => []],
        ['key' => 'H', 'user_list' => []],
        ['key' => 'I', 'user_list' => []],
        ['key' => 'J', 'user_list' => []],
        ['key' => 'K', 'user_list' => []],
        ['key' => 'L', 'user_list' => []],
        ['key' => 'M', 'user_list' => []],
        ['key' => 'N', 'user_list' => []],
        ['key' => 'O', 'user_list' => []],
        ['key' => 'P', 'user_list' => []],
        ['key' => 'Q', 'user_list' => []],
        ['key' => 'R', 'user_list' => []],
        ['key' => 'S', 'user_list' => []],
        ['key' => 'T', 'user_list' => []],
        ['key' => 'U', 'user_list' => []],
        ['key' => 'V', 'user_list' => []],
        ['key' => 'W', 'user_list' => []],
        ['key' => 'X', 'user_list' => []],
        ['key' => 'Y', 'user_list' => []],
        ['key' => 'Z', 'user_list' => []],
    ];
    foreach ($list as $key => $value) {
        $found_key = array_search(strtoupper($value['alias_name']), array_column($char_array, 'key'));
        $char_array[$found_key]['user_list'][] = $value;
    }
    return $char_array;
}

/**
 * Fun convertGCJ02ToBD09 中国正常GCJ02坐标---->百度地图BD09坐标
 *
 * @param double $lat 纬度
 * @param double $lng 经度
 *
 * @return array
 */
function convertGCJ02ToBD09($lat, $lng)
{
    $xPi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng;
    $y = $lat;
    $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $xPi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $xPi);
    $lng = $z * cos($theta) + 0.0065;
    $lat = $z * sin($theta) + 0.006;

    $arr = [];
    $arr['lng'] = $lng;
    $arr['lat'] = $lat;

    return $arr;
}

/**
 * Fun convertBD09ToGCJ02 百度地图BD09坐标---->中国正常GCJ02坐标
 *
 * @param double $lat 纬度
 * @param double $lng 经度
 *
 * @return array
 */
function convertBD09ToGCJ02($lat, $lng)
{
    $xPi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $xPi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $xPi);
    $lng = $z * cos($theta);
    $lat = $z * sin($theta);

    $arr = [];
    $arr['lng'] = $lng;
    $arr['lat'] = $lat;

    return $arr;
}

/*
 * 生成用户token
 */
function cmf_generate_wechat_user_token($userId, $deviceType)
{
    $userTokenQuery = WechatUserTokenModel::where(['user_id' => $userId, 'device_type' => $deviceType])->find();
    $currentTime = time();
    $expireTime = $currentTime + 24 * 3600 * 180;
    $token = md5(uniqid()) . md5(uniqid());
    if (empty($findUserToken)) {
        $model = new WechatUserTokenModel([
            'token' => $token,
            'user_id' => $userId,
            'expire_time' => $expireTime,
            'device_type' => $deviceType
        ]);
        $model->save();
    } else {
        if ($findUserToken['expire_time'] > time() && !empty($findUserToken['token'])) {
            $token = $findUserToken['token'];
        } else {
            $userTokenQuery->token = $token;
            $userTokenQuery->expire_time = $expireTime;
            $userTokenQuery->save();
        }
    }
    return $token;
}
