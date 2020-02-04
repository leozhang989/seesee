<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\Notice;
use App\Models\NoticeLog;
use App\Models\Server;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppusersController extends Controller
{
    public function getUserInfo(Request $request){
        $response = [];
        $now = time();
        $nowDate = strtotime(date('Ymd', $now));
        if($request->filled('device_code')) {
            $hasNewerVersion = 0;
            $leftDays = 90;
            $testflightContent = $testflightUrl = '';
            //update version settings
            if($request->filled('version')){
                $appVersions = AppVersion::orderBy('expired_date', 'DESC')->pluck('app_version')->toArray();
                $latestVersionRes = AppVersion::orderBy('expired_date', 'DESC')->first();
                if(!in_array($request->input('version'), $appVersions)){
                    $latestVersionRes = AppVersion::create([
                        'app_version' => $request->input('version'),
                        'content' => '',
                        'testflight_url' => SystemSetting::getValueByName('testflightUrl') ? : '',
                        'expired_date' => $nowDate + 90 * 24 * 3600
                    ]);
                }
                $diffDateInt = $latestVersionRes['expired_date'] - $nowDate;
                $leftDays = floor($diffDateInt / (3600 * 24));
                $testflightContent = $latestVersionRes['content'];
                $testflightUrl = $latestVersionRes['testflight_url'];
                if($latestVersionRes['app_version'] != $request->input('version'))
                    $hasNewerVersion = 1;
            }
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
            if(empty($deviceInfo)){
                $uuid = $this->generateUUID();
                $freeDays = SystemSetting::getValueByName('freeDays');
                $deviceInfo = Device::create([
                    'uuid' => $uuid,
                    'device_code' => $request->input('device_code'),
                    'is_master' => 0,
                    'status' => 1,
                    'free_vip_expired' => strtotime('+' . $freeDays . ' day'),
                    'uid' => 0
                ]);

            }

            //支付未上线延长用户有效期
            if($deviceInfo['free_vip_expired'] - $now < 432000){
                $deviceInfo->free_vip_expired = $deviceInfo['free_vip_expired'] + 432000;
                $deviceInfo->save();
            }

            //if has new notice now
            $newNotice = 0;
            $noticeUrl = '';
            $nowDate = date('Y-m-d H:i:s', $now);
            $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
            if($latestNotice) {
                $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                $newNotice = $userNoticeLog ? 0 : 1;
                $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ? : '';
            }

            $response['userInfo'] = [
                'uuid' => $deviceInfo['uuid'] ? : '',
//                'freeVipExpired' => 0,
                'vipExpired' => $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0,
                'isVip' => 1,
                'email' => '',
                'hasNewNotice' => $newNotice,
                'noticeUrl' => $noticeUrl,
                'paymentUrl' => action('PayController@list', ['token' => $deviceInfo['uuid']]),
            ];

//            $deviceInfo = Appuser::where('uuid', $deviceInfo['uuid'])->first(['uuid', 'free_vip_expired as freeVipExpired', 'vip_expired', 'email', 'gid']);
//            $userInfo = Appuser::create([
//                'name' => '',
//                'gid' => 0,
//                'email' => '',
//                'password' => '',
//                'phone' => '',
//                'free_vip_expired' => strtotime('+' . $freeDays . ' day'),
//                'vip_expired' => 0,
//                'vip_left_time' => 0,
//                'uuid' => $uuid,
//            ]);

            $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);

            $response['testflight']['url'] = $testflightUrl ? : '';
            $response['testflight']['leftDays'] = $leftDays;
            $response['testflight']['hasNewer'] = $hasNewerVersion;
            $response['testflight']['content'] = $testflightContent;
            return response()->json(['msg' => 'success', 'data' => $response, 'code' => 200]);
        }
        return response()->json(['msg' => '参数错误', 'data' => $response, 'code' => 202]);
    }

    protected function generateUUID(){
        $lastUser = DB::table('devices')
            ->latest()
            ->first();
        $lastUuid = $lastUser && $lastUser->uuid ? $lastUser->uuid : '1000011';
        $length = strlen($lastUuid) - 1;
        $uuid = substr($lastUuid, 0, $length) + 1 . random_int(0, 9);
        return $uuid;
    }


    public function setAccount(Request $request){

        return response()->json(['msg' => 'success', 'data' => [], 'code' => 200]);
    }


    public function login(Request $request){
        if($request->filled('password') && $request->filled('email') && $request->filled('device_code')) {
            $user = Appuser::where('email', $request->input('email'))->where('password', MD5($request->input('password')))->first();
            if (!empty($user)) {
                $now = time();
                //设置用户session
                session(['user' => $user['id']]);

                //检测设备数
                $exsitedDevicesCount = Device::where('uid', $user['id'])->count();
                $maxSettings = SystemSetting::getValueByName('seeMaxDevices') ? : 3;
                if($exsitedDevicesCount >= $maxSettings)
                    return response()->json(['data' => [], 'msg' => '登录失败，只支持' . $maxSettings . '台设备绑定。', 'code' => 202]);

                $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
                if($deviceInfo) {
                    $deviceInfo->uid = $user['id'];
                    $deviceInfo->save();
                }else
                    return response()->json(['data' => [], 'msg' => '登录失败，设备不存在', 'code' => 202]);

//                $totalIntegral = $user['integral'];
                unset($user['id'], $user['created_at'], $user['updated_at'], $user['name']);

                //if has new notice now
                $newNotice = 0;
                $noticeUrl = '';
                $nowDate = date('Y-m-d H:i:s', $now);
                $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                if($latestNotice) {
                    $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                    $newNotice = $userNoticeLog ? 0 : 1;
                    $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ? : '';
                }

                $vipExpiredTime = $user['vip_expired'] > $now ? $user['vip_expired'] : $now;
                $response['userInfo'] = [
                    'uuid' => $deviceInfo['uuid'] ? : '',
                    'vipExpired' => $deviceInfo['free_vip_expired'] > $vipExpiredTime ? $deviceInfo['free_vip_expired'] - $now : $vipExpiredTime - $now,
                    'isVip' => 1,
                    'email' => $request->input('email'),
                    'hasNewNotice' => $newNotice,
                    'noticeUrl' => $noticeUrl,
                    'paymentUrl' => action('PayController@list', ['token' => $deviceInfo['uuid']]),
                ];

                $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);

                //update version settings
                $hasNewerVersion = 0;
                $leftDays = 90;
                $testflightContent = $testflightUrl = '';
                if($request->filled('version')){
                    $appVersions = AppVersion::orderBy('expired_date', 'DESC')->pluck('app_version')->toArray();
                    $latestVersionRes = AppVersion::orderBy('expired_date', 'DESC')->first();
                    if(!in_array($request->input('version'), $appVersions)){
                        $latestVersionRes = AppVersion::create([
                            'app_version' => $request->input('version'),
                            'content' => '',
                            'testflight_url' => SystemSetting::getValueByName('testflightUrl') ? : '',
                            'expired_date' => $nowDate + 90 * 24 * 3600
                        ]);
                    }
                    $diffDateInt = $latestVersionRes['expired_date'] - $nowDate;
                    $leftDays = floor($diffDateInt / (3600 * 24));
                    $testflightContent = $latestVersionRes['content'];
                    $testflightUrl = $latestVersionRes['testflight_url'];
                    if($latestVersionRes['app_version'] != $request->input('version'))
                        $hasNewerVersion = 1;
                }

                $response['testflight']['url'] = $testflightUrl ? : '';
                $response['testflight']['leftDays'] = $leftDays;
                $response['testflight']['hasNewer'] = $hasNewerVersion;
                $response['testflight']['content'] = $testflightContent;

                return response()->json(['data' => $response, 'msg' => '登陆成功', 'code' => 200]);
            }
            return response()->json(['data' => [], 'msg' => '账号或密码错误', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '登陆失败，请重试！', 'code' => 202]);
    }

    public function logout(Request $request){        //清空用户session
        if ($request->session()->has('user')) {
            $request->session()->forget('user');
        }

        return response()->json(['data' => [],'msg' => '登出成功', 'code' => 200]);
    }

}
