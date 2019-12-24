<?php

use Illuminate\Support\Facades\DB;

/**
 * 除去数组中的空值和签名参数、然后排序、然后生成md5签名
 * @param $params 签名参数组
 * @param $secret 签名密钥
 * @return array 去掉空值与签名参数后并排序的新签名参数组
 */
function generateSign($params, $secret) {
    $params = paramsFilter($params);//去空
    $params = argSort($params);//排序
    $preSign = generateHttpBuildQuery($params);//生成待签名的字串
//    $preSign = generateStr($params);
    $result = [];
    $result['params'] = $params;
    $result['result']['sign'] = md5Sign($preSign, $secret);
    $result['result']['preSign'] = $preSign;
    return $result;
}
/**
 * 除去数组中的空值和签名参数
 * @param $params 签名参数组
 * @return array 去掉空值与签名参数后的新签名参数组
 */
function paramsFilter($params)
{
    $paramsFilter = array();
    foreach ($params as $key => $value) {
        if ($key == "sign" || $key == "sign_type" || checkEmpty($value) === true) {
            continue;
        } else {
            $paramsFilter[$key] = $params[$key];
        }
    }
    return $paramsFilter;
}
/**
 * 检测值是否为空
 * @param    string                   		$value 待检测的值
 * @return   boolean                     	 null | "" | unsset 返回 true;
 */
function checkEmpty($value) {
    if (!isset($value))
        return true;
    if ($value === null)
        return true;
    if (trim($value) === "")
        return true;
    return false;
}
/**
 * 对数组排序
 * @param $params 排序前的数组
 * @return array 排序后的数组
 */
function argSort($params) {
    ksort($params);
    reset($params);
    return $params;
}
/**
 * 方法1：把数组所有元素，按照“key1=value1&key2=value2”的模式拼接成字符串
 * @param $params 需要拼接的一维数组
 * @return string
 */
function generateHttpBuildQuery($params) {
    $arg = http_build_query($params);
    $arg = urldecode($arg);

    //如果存在转义字符，那么去掉转义
//    if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

    return $arg;
}
/**
 * 方法2：把数组所有元素，按照“key1value1key2value2”的模式拼接成字符串
 * @param $params 需要拼接的一维数组
 * @return string
 */
function generateStr($params) {
    $arg  = "";
    foreach ($params as $key => $value) {
        $arg.=$key.$value;
    }
    //如果存在转义字符，那么去掉转义
    $arg = urldecode($arg);

    return $arg;
}
/**
 * 签名字符串
 * @param $preSign 需要签名的字符串
 * @param $secret 私钥
 * @return string 签名结果
 */
function md5Sign($preSign, $secret) {
    $preSign = $secret . $preSign;

    return strtolower(md5($preSign));
}

function paddleKey(){
//    $paddleKeyPath = public_path('paddle/paddle.' . getenv('APP_ENV') . '.key');
    $paddleKeyPath = public_path('paddle/paddle.production.key');
    return file_get_contents($paddleKeyPath);
}