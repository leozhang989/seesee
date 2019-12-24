<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;

class CheckSign
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = $request->all();
        if (empty($params) || empty($params['sign']) || empty($params['timestamp']) || empty($params['appId'])) {
            $code = -1;
            $msg = '参数错误';
        }else{
            $sign = $params['sign'];
            unset($params['sign']);
            $secret = SystemSetting::getValueByName($params['appId']);
            $result = generateSign($params, $secret);
            if(time() - $params['timestamp'] >= 600){
                $code = -1;
                $msg = '请求超时，请重新请求，当前时间戳是 ' . time();
//                $msg = '请求超时，请重新请求';
            }elseif($result['result']['sign'] != $sign){
                $code = -1;
                $msg = '签名错误，正确签名是 ' . $result['result']['sign'];
//                $msg = '签名错误';
            }else
                return $next($request);
        }
        return response()->json(['msg' => $msg, 'data' => [], 'code' => $code]);
    }
}
