<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\devicesUuidRelations;
use App\Models\Notice;
use App\Models\NoticeLog;
use App\Models\OldDevice;
use App\Models\Order;
use App\Models\RechargeLogs;
use App\Models\Seedevice;
use App\Models\Seeuser;
use App\Models\SeeVersion;
use App\Models\Server;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserInfoController extends Controller
{
    public function register(Request $request){
        if($request->filled('email') && $request->filled('password') && $request->filled('device_code')) {
            //check email
            if(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
                return response()->json(['data' => [], 'msg' => '邮箱格式错误，请输入正确的邮箱', 'code' => 202]);

            $isExisted = Seeuser::where('email', $request->input('email'))->first(['id']);
            if ($isExisted)
                return response()->json(['data' => [], 'msg' => '该邮箱已注册过，请直接登录', 'code' => 202]);

            //注册时写入设备
            $uuid = $this->generateUUID();
            if(empty($uuid))
                return response()->json(['msg' => '登录失败，请重试', 'data' => [], 'code' => 202]);

            $deviceResRela = devicesUuidRelations::where('device_code', $request->input('device_code'))->first();
            $now = time();
            if (empty($deviceResRela)) {
                $freeDays = SystemSetting::getValueByName('freeDays');
                $freeVipExpired = strtotime('+' . $freeDays . ' day');
                $deviceResRela = devicesUuidRelations::create([
                    'uuid' => $uuid,
                    'device_code' => $request->input('device_code'),
                    'free_vip_expired' => $freeVipExpired,
                    'uid' => 0
                ]);
            }else{
                $freeVipExpired = $deviceResRela['free_vip_expired'];
            }
            $deviceRes = Seedevice::where('device_code', $request->input('device_code'))->first();
            if(empty($deviceRes)) {
                $deviceRes = Seedevice::create([
                    'uuid' => $uuid,
                    'device_code' => $request->input('device_code'),
                    'is_master' => 0,
                    'status' => 1,
                    'free_vip_expired' => $freeVipExpired,
                    'uid' => 0
                ]);
            }

            $insertData = [
                'name' => '',
                'gid' => 0,
                'email' => $request->input('email'),
                'password' => MD5($request->input('password')),
                'phone' => '',
                'free_vip_expired' => $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : 0,
                'vip_expired' => $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : 0,
                'vip_left_time' => 0,
                'uuid' => $uuid
            ];
            $userInfo = Seeuser::create($insertData);
            if ($userInfo) {
                //update device uid
                $deviceRes->uid = $userInfo['id'];
                $deviceRes->save();
                if($deviceResRela) {
                    $deviceResRela->uid = $userInfo['id'];
                    $deviceResRela->save();
                }

                //if has new notice now
                $newNotice = 0;
                $noticeUrl = '';
                $nowDate = date('Y-m-d H:i:s', $now);
                $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                if ($latestNotice) {
                    $userNoticeLog = NoticeLog::where('uuid', $uuid)->where('notice_id', $latestNotice['id'])->first();
                    $newNotice = $userNoticeLog ? 0 : 1;
                    $noticeUrl = config('app.url') . action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $uuid], false) ?: '';
                }

                $vipExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] : $now;
                $totalExpiredTime = $deviceRes['free_vip_expired'] > $vipExpiredTime ? $deviceRes['free_vip_expired'] - $now : $vipExpiredTime - $now;
                $response['userInfo'] = [
                    'uuid' => $uuid,
                    'vipExpired' => $totalExpiredTime,
                    'isVip' => $totalExpiredTime > 0 ? 1 : 0,
                    'email' => trim($request->input('email')),
                    'hasNewNotice' => $newNotice,
                    'noticeUrl' => $noticeUrl,
                    'paymentUrl' => config('app.url') . action('PayController@list', ['token' => $uuid], false),
                ];

                if($request->filled('device_identifier')){
                    $deviceRes->device_identifier = $request->input('device_identifier', '');
                    $deviceRes->save();
                }

                return response()->json(['data' => $response, 'msg' => '注册成功', 'code' => 200]);
            }
        }
        return response()->json(['data' => [], 'msg' => '注册失败，请重试！', 'code' => 202]);
    }

    public function login(Request $request){
        if($request->filled('password') && $request->filled('email') && $request->filled('device_code')) {
            $userExisted = Seeuser::where('email', trim($request->input('email')))->count();
            if($userExisted > 0) {
                $user = Seeuser::where('email', trim($request->input('email')))->where('password', MD5($request->input('password')))->first();
                if (!empty($user)) {
                    $now = time();
                    //设置用户session
                    session(['user' => $user['id']]);

                    $allDeviceCodes = Seedevice::where('uid', $user['id'])->pluck('device_code')->toArray();
                    if(!in_array($request->input('device_code'), $allDeviceCodes)){
                        //检测设备数
                        $exsitedDevicesCount = count($allDeviceCodes);
                        $maxSettings = SystemSetting::getValueByName('seeMaxDevices') ?: 3;
                        if ($exsitedDevicesCount >= $maxSettings)
                            return response()->json(['data' => [], 'msg' => '登录失败，只支持' . $maxSettings . '台设备绑定。', 'code' => 202]);
                    }
                    $uuid = $user['uuid'] ?? '';
                    if(empty($uuid)){
                        $uuid = $this->generateUUID();
                        $user->uuid = $uuid;
                        $user = $user->save();
                    }

                    $deviceResRela = devicesUuidRelations::where('device_code', $request->input('device_code'))->first();
                    if (empty($deviceResRela)) {
                        $freeDays = SystemSetting::getValueByName('freeDays');
                        $freeVipExpired = strtotime('+' . $freeDays . ' day');
                        devicesUuidRelations::create([
                            'uuid' => $uuid,
                            'device_code' => $request->input('device_code'),
                            'free_vip_expired' => $freeVipExpired,
                            'uid' => $user['id']
                        ]);
                    }else{
                        $freeVipExpired = $deviceResRela['free_vip_expired'];
                    }
                    $deviceInfo = Seedevice::where('device_code', $request->input('device_code'))->first();
                    if(empty($deviceInfo)) {
                        $deviceInfo = Seedevice::create([
                            'uuid' => $uuid,
                            'device_code' => $request->input('device_code'),
                            'is_master' => 0,
                            'status' => 1,
                            'free_vip_expired' => $freeVipExpired,
                            'uid' => $user['id'],
                            'device_model' => trim($request->input('model', ''))
                        ]);
                    }else{
                        $deviceInfo->uid = $user['id'];
                        $deviceInfo->uuid = $uuid;
                        $deviceInfo->save();
                    }
                    unset($user['id'], $user['created_at'], $user['updated_at'], $user['name']);

                    //if has new notice now
                    $newNotice = 0;
                    $noticeUrl = '';
                    $nowDate = date('Y-m-d H:i:s', $now);
                    $latestNotice = Notice::where('id', '<', 9)->where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                    if($user['is_permanent_vip'] == 1){
                        $webpLatestNotice = Notice::find(9);
                    }else{
                        $webpLatestNotice = Notice::find(10);
                    }
                    if ($latestNotice) {
                        $userNoticeLog = NoticeLog::where('uuid', $uuid)->where('notice_id', $latestNotice['id'])->first();
                        $newNotice = $userNoticeLog ? 0 : 1;
                        $noticeUrl = config('app.url') . action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $uuid], false) ?: '';
                    }
                    if($webpLatestNotice){
                        $newNotice = 1;
                        $noticeUrl = $user['is_permanent_vip'] == 1 ? action('KangTransferController@webpermanentTransferPage', ['uuid' => $uuid]) : action('KangTransferController@webOrdTransferPage', ['uuid' => $uuid]);
                    }

                    if($user['is_permanent_vip'] == 1 && $request->input('device_code', '') === $user['permanent_device'] && $user['permanent_expired'] > $now){
                        $totalExpiredTime = $user['permanent_expired'] - $now;
                    }else {
                        $vipExpiredTime = $user['vip_expired'] > $now ? $user['vip_expired'] : $now;
                        $totalExpiredTime = $deviceInfo['free_vip_expired'] > $vipExpiredTime ? $deviceInfo['free_vip_expired'] - $now : $vipExpiredTime - $now;
                    }
                    $response['userInfo'] = [
                        'uuid' => $uuid,
                        'vipExpired' => $totalExpiredTime,
                        'isVip' => $totalExpiredTime > 0 ? 1 : 0,
                        'email' => trim($request->input('email')),
                        'hasNewNotice' => $newNotice,
                        'noticeUrl' => $noticeUrl,
                        'paymentUrl' => config('app.url') . action('PayController@list', ['token' => $uuid], false),
                        'supportPayPage' => config('app.url') . action('DownloadController@seeSupportPay', ['uuid' => $uuid], false)
                    ];

                    if($request->filled('device_identifier')){
                        $deviceInfo->device_identifier = $request->input('device_identifier');
                        $deviceInfo->save();
                    }
                    //last login
                    $user->last_login = Carbon::now();
                    $user->save();

                    return response()->json(['data' => $response, 'msg' => '登陆成功', 'code' => 200]);
                }
                return response()->json(['data' => [], 'msg' => '账号或密码错误', 'code' => 202]);
            }
            return response()->json(['data' => [], 'msg' => '账号不存在', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '登陆失败，请重试！', 'code' => 202]);
    }

    public function queryUserVip(Request $request){
        if($request->filled('device_code')){
            $now = time();
            $deviceInfo = Seedevice::where('device_code', $request->input('device_code'))->first();
            $userInfo = [];
            if($request->filled('email'))
                $userInfo = Seeuser::where('email', $request->input('email'))->first();

            if(empty($deviceInfo) && empty($userInfo))
                return response()->json(['msg' => '用户不存在', 'data' => '', 'code' => 202]);

            //新版testFlight版本信息
            $testFlight = [];
            $testflightUrl = SystemSetting::getValueByName('seeTestFlightUrl') ? : '';
            $testflightContent = '';
            $hasNewerVersion = 0;
            if($request->filled('version')){
                if($userInfo['is_permanent_vip'] == 1){
                    $latestVersionRes = SeeVersion::find(3);
                }else{
                    $latestVersionRes = SeeVersion::find(4);
                }
                if ($latestVersionRes && ($request->input('version', 0) < $latestVersionRes['app_version'])) {
                    if($userInfo['is_permanent_vip'] == 1){
                        $hasNewerVersion = 1;
                        $testflightUrl = action('KangTransferController@webpermanentTransferPage', ['uuid' => $userInfo['uuid']]) ?: '';
                        $testflightContent = '亲爱的用户，新版本新模式终于来了！
最新模式摒弃了testflight不稳定的方式，采用思科客户端【Cisco AnyConnect】为大家提供链接服务。
大厂品质，稳定可靠，欢迎体验。
有任何问题联系fengchi@pm.me';
                    }else {
                        $hasNewerVersion = 1;
                        $testflightUrl = action('KangTransferController@webOrdTransferPage', ['uuid' => $userInfo['uuid']]) ?: '';
                        $testflightContent = '亲爱的用户，新版本新模式终于来了！
最新模式摒弃了testflight不稳定的方式，采用思科客户端【Cisco AnyConnect】为大家提供链接服务。
大厂品质，稳定可靠，欢迎体验。
有任何问题联系fengchi@pm.me';
                    }
                }
            }
            $testFlight['url'] = $testflightUrl;
            $testFlight['hasNewer'] = $hasNewerVersion;
            $testFlight['content'] = $testflightContent;

            //展示公告
            $announcement = Announcement::where('id', '<', 8)->orderBy('id', 'DESC')->first();
            if($userInfo['is_permanent_vip']){
                $announcementwebp = Announcement::find(8);
            }else{
                $announcementwebp = Announcement::find(9);
            }
            $userAnnouncement['online'] = 0;
            $userAnnouncement['content'] = $userAnnouncement['redirect_url'] = '';
            if($announcement){
                $userAnnouncement['online'] = $announcement['online'] ? 1 : 0;
                $userAnnouncement['content'] = $announcement['content'] ? : '';
                $userAnnouncement['redirect_url'] = $announcement['redirect_url'] ? : '';
            }
            if($announcementwebp){
                $userAnnouncement['online'] = 1;
                $userAnnouncement['content'] = $announcementwebp['content'];
                $userAnnouncement['redirect_url'] = $userInfo['is_permanent_vip'] ? action('KangTransferController@webpermanentTransferPage', ['uuid' => $userInfo['uuid']]) : action('KangTransferController@webOrdTransferPage', ['uuid' => $userInfo['uuid']]);
            }

            $totalExpiredTime = 0;
            if($deviceInfo)
                $totalExpiredTime = $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0;
            if($userInfo){
                if($userInfo['is_permanent_vip'] === 1 && $request->input('device_code') === $userInfo['permanent_device'] && $userInfo['permanent_expired'] > $now){  //permanent_vip login
                    $totalExpiredTime = $userInfo['permanent_expired'] - $now;
                }else{
                    $totalExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] - $now : 0;
                }
            }

            $isSupportPay = 0;
            if(in_array($deviceInfo['uuid'], ['1023492', '1027653', '1023501']))
                $isSupportPay = 1;

            if($request->filled('device_identifier')){
                $deviceInfo->device_identifier = $request->input('device_identifier', '');
                $deviceInfo->save();
            }

            return response()->json(['msg' => '查询成功', 'data' => ['vipExpired' => $totalExpiredTime, 'testflight' => $testFlight, 'announcement' => $userAnnouncement, 'isSupportPay' => $isSupportPay], 'code' => 200]);
        }
        return response()->json(['msg' => '查询失败，参数异常', 'data' => '', 'code' => 202]);
    }

    protected function generateUUID(){
        $uuid = '';
        for ($x=0; $x<=5; $x++){
            $seeLastUser = DB::table('seeusers')
                ->where('uuid', '<>', '')
                ->orderBy('id', 'DESC')
                ->first();
            $lastUuid = '';
            if($seeLastUser && $seeLastUser->uuid && strlen($seeLastUser->uuid) == 7){
                $lastUuid = $seeLastUser->uuid;
            }
            $lastUuid = $lastUuid ? : '1210011';
            $length = strlen($lastUuid) - 3;
            $newNum = str_pad((substr($lastUuid, 2, $length) + 1),  4, '0', STR_PAD_LEFT);
            $uuid = '12' . $newNum . random_int(0, 9);
            $ex = Seedevice::where('uuid', $uuid)->first();
            if(empty($ex))
                break;
        }

        return $uuid;
    }

}
