<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Appuser;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\devicesUuidRelations;
use App\Models\FengDevice;
use App\Models\FengUser;
use App\Models\FlowerAdServers;
use App\Models\FlowerTransferLogs;
use App\Models\FlowerUser;
use App\Models\FlowerUsers;
use App\Models\FlowerVipSetLogs;
use App\Models\Notice;
use App\Models\NoticeLog;
use App\Models\SeeVersion;
use App\Models\Server;
use App\Models\SystemSetting;
use App\Models\TransferLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AesController;
use App\Models\AccountServers;
use App\Models\VipServer;
use App\Models\ServersList;
use App\Models\AppServersList;
use Illuminate\Support\Facades\Schema;

class AppusersController extends Controller
{
    public function getUserInfo(Request $request){
        $response = [];
        $now = time();
        if($request->filled('device_code')) {
            //update version settings
            $hasNewerVersion = 0;
            $leftDays = 90;
            $testflightContent = '';
            $testflightUrl = SystemSetting::getValueByName('seeTestFlightUrl') ? : '';
            if($request->filled('version')){
                $latestVersionRes = AppVersion::where('online', 1)->orderBy('app_version', 'DESC')->first();
                $userVersionNo = $request->input('version', 0);
                if($userVersionNo < $latestVersionRes['app_version']) {
                    $hasNewerVersion = 1;
                    $testflightContent = $latestVersionRes['content'];
                }
            }

            $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
            if(empty($deviceInfo)){
                $freeDays = SystemSetting::getValueByName('freeDays');
                //查询关联表是否已经有老设备的关联记录
                $deviceRes = Schema::hasTable('devices_uuid_relations') ? devicesUuidRelations::where('device_code', $request->input('device_code'))->first() : [];
                if($deviceRes){
                    $uuid = $deviceRes['uuid'];
                    $freeVipExpired = $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : $now;
                }else{
                    $uuid = $this->generateUUID();
                    if(empty($uuid))
                        return response()->json(['msg' => '设备登录失败，请重试', 'data' => [], 'code' => 202]);

                    $freeVipExpired = strtotime('+' . $freeDays . ' day');
                    if(Schema::hasTable('devices_uuid_relations')){
                        devicesUuidRelations::create([
                            'uuid' => $uuid,
                            'device_code' => $request->input('device_code'),
                            'free_vip_expired' => $freeVipExpired,
                            'uid' => 0
                        ]);
                    }
                }
                $deviceInfo = Device::create([
                    'uuid' => $uuid,
                    'device_code' => $request->input('device_code'),
                    'is_master' => 0,
                    'status' => 1,
                    'free_vip_expired' => $freeVipExpired,
                    'uid' => 0
                ]);

            }

            //支付未上线延长用户有效期
//            if($deviceInfo['free_vip_expired'] - $now < 432000){
//                $deviceInfo->free_vip_expired = $deviceInfo['free_vip_expired'] + 432000;
//                $deviceInfo->save();
//            }

            //if has new notice now
            $newNotice = 0;
            $noticeUrl = '';
            $nowDate = date('Y-m-d H:i:s', $now);
            $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
            if($latestNotice) {
                if($request->input('version') != 3) {
                    $newNotice = 1;
                    if(empty($deviceInfo['uid'])){
                        $token = md5($deviceInfo['uuid'] . 'seedevicezhuanyi');
                        $noticeUrl = action('AppusersController@seeDeviceZhuanyiPage', ['uuid' => $deviceInfo['uuid'], 'token' => $token]) ? : '';
                    }else{
                        $noticeUrl = action('AppusersController@seeAccountZhuanyiPage', ['uuid' => $deviceInfo['uuid']]) ? : '';
                    }
                }else{
                    $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                    $newNotice = $userNoticeLog ? 0 : 1;
                    $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ?: '';
                }
            }

            $response['userInfo'] = [
                'uuid' => $deviceInfo['uuid'] ? : '',
//                'freeVipExpired' => 0,
                'vipExpired' => $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0,
                'isVip' => $deviceInfo['free_vip_expired'] > $now ? 1 : 0,
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

    public function login(Request $request){
        if($request->filled('password') && $request->filled('email') && $request->filled('device_code')) {
            $userExisted = Appuser::where('email', trim($request->input('email')))->count();
            if($userExisted > 0) {
                $user = Appuser::where('email', trim($request->input('email')))->where('password', MD5($request->input('password')))->first();
                if (!empty($user)) {
                    $now = time();
                    //设置用户session
                    session(['user' => $user['id']]);

                    $allDeviceCodes = Device::where('uid', $user['id'])->pluck('device_code')->toArray();
                    if(!in_array($request->input('device_code'), $allDeviceCodes)){
                        //检测设备数
                        $exsitedDevicesCount = count($allDeviceCodes);
                        $maxSettings = SystemSetting::getValueByName('seeMaxDevices') ?: 3;
                        if ($exsitedDevicesCount >= $maxSettings)
                            return response()->json(['data' => [], 'msg' => '登录失败，只支持' . $maxSettings . '台设备绑定。', 'code' => 202]);
                    }
                    //小花转移的永久VIP用户只能登录一台设备
//                    $transferUser = FlowerTransferLogs::where('email', trim($request->input('email')))->where('vip_type', 'permanent-vip')->first();
//                    if($transferUser && $transferUser['device_code'] !== $request->input('device_code'))
//                        return response()->json(['data' => [], 'msg' => '永久用户仅限一台设备永久使用，不支持多设备同时登录', 'code' => 202]);

                    $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
                    if ($deviceInfo) {
                        $uuid = $deviceInfo['uuid'];
                        $deviceInfo->uid = $user['id'];
                        $deviceInfo->save();
                    } else {
                        $freeDays = SystemSetting::getValueByName('freeDays');
                        //查询关联表是否已经有老设备的关联记录
                        $deviceRes = Schema::hasTable('devices_uuid_relations') ? devicesUuidRelations::where('device_code', $request->input('device_code'))->first() : [];
                        if($deviceRes){
                            $uuid = $deviceRes['uuid'];
                            $freeVipExpired = $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : $now;
                        }else{
                            $uuid = $this->generateUUID();
                            if(empty($uuid))
                                return response()->json(['msg' => '设备登录失败，请重试', 'data' => [], 'code' => 202]);

                            $freeVipExpired = strtotime('+' . $freeDays . ' day');
                            if(Schema::hasTable('devices_uuid_relations')) {
                                devicesUuidRelations::create([
                                    'uuid' => $uuid,
                                    'device_code' => $request->input('device_code'),
                                    'free_vip_expired' => $freeVipExpired,
                                    'uid' => $user['id']
                                ]);
                            }
                        }
                        $deviceInfo = Device::create([
                            'uuid' => $uuid,
                            'device_code' => $request->input('device_code'),
                            'is_master' => 0,
                            'status' => 1,
                            'free_vip_expired' => $freeVipExpired,
                            'uid' => $user['id'],
                            'device_model' => trim($request->input('model', ''))
                        ]);
                    }

                    //记录转移
                    if ($request->input('version') == 3 && $deviceInfo['transfered'] === 0) {
                        $deviceInfo->transfered = 1;
                        $deviceInfo->transfered_time = $now;
                        $deviceInfo->save();
                    }

//                $totalIntegral = $user['integral'];
                    unset($user['id'], $user['created_at'], $user['updated_at'], $user['name']);

                    //if has new notice now
                    $newNotice = 0;
                    $noticeUrl = '';
                    $nowDate = date('Y-m-d H:i:s', $now);
                    $today = strtotime(date('Y-m-d', $now));
                    $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                    if ($latestNotice) {
                        if($request->input('version') != 3) {
                            $newNotice = 1;
                            if(empty($deviceInfo['uid'])){
                                $token = md5($uuid . 'seedevicezhuanyi');
                                $noticeUrl = action('AppusersController@seeDeviceZhuanyiPage', ['uuid' => $uuid, 'token' => $token]) ? : '';
                            }else{
                                $noticeUrl = action('AppusersController@seeAccountZhuanyiPage', ['uuid' => $uuid]) ? : '';
                            }
                        }else{
                            $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                            $newNotice = $userNoticeLog ? 0 : 1;
                            $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ?: '';
                        }
                    }

                    $vipExpiredTime = $user['vip_expired'] > $now ? $user['vip_expired'] : $now;
                    $totalExpiredTime = $deviceInfo['free_vip_expired'] > $vipExpiredTime ? $deviceInfo['free_vip_expired'] - $now : $vipExpiredTime - $now;
                    $response['userInfo'] = [
                        'uuid' => $deviceInfo['uuid'] ?: '',
                        'vipExpired' => $totalExpiredTime,
                        'isVip' => $totalExpiredTime > 0 ? 1 : 0,
                        'email' => trim($request->input('email')),
                        'hasNewNotice' => $newNotice,
                        'noticeUrl' => $noticeUrl,
                        'paymentUrl' => action('PayController@list', ['token' => $deviceInfo['uuid']]),
                    ];

                    $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);

                    //update version settings
                    $hasNewerVersion = 0;
                    $leftDays = 90;
                    $testflightContent = '';
                    $testflightUrl = SystemSetting::getValueByName('seeTestFlightUrl') ? : '';
                    if($request->filled('version')){
                        $latestVersionRes = AppVersion::where('online', 1)->orderBy('app_version', 'DESC')->first();
                        $userVersionNo = $request->input('version', 0);
                        if($userVersionNo < $latestVersionRes['app_version']) {
                            $hasNewerVersion = 1;
                            $testflightContent = $latestVersionRes['content'];
                        }
                    }

//                    if ($request->filled('version')) {
//                        $appVersions = AppVersion::where('online', 1)->orderBy('id', 'DESC')->pluck('app_version')->toArray();
//                        $latestVersionRes = AppVersion::where('online', 1)->orderBy('id', 'DESC')->first();
//                        $userVersion = AppVersion::where('app_version', $request->input('version'))->first();
//                        if (!in_array($request->input('version'), $appVersions)) {
//                            $latestVersionRes = AppVersion::create([
//                                'app_version' => $request->input('version'),
//                                'content' => '',
//                                'testflight_url' => SystemSetting::getValueByName('testflightUrl') ?: '',
//                                'expired_date' => $today + 90 * 24 * 3600,
//                                'online' => 0
//                            ]);
//                        }else{
//                            if($userVersion['online'] === 0)
//                                $latestVersionRes = $userVersion;
//                        }
//                        $diffDateInt = $userVersion['expired_date'] - $today > 0 ? $userVersion['expired_date'] - $today : 0;
//                        $leftDays = floor($diffDateInt / (3600 * 24));
//                        $testflightContent = $userVersion['content'];
//                        $testflightUrl = $userVersion['testflight_url'];
//                        if ($latestVersionRes['app_version'] != $request->input('version')){
//                            $hasNewerVersion = 1;
//                            $testflightContent = $latestVersionRes['content'];
//                            $testflightUrl = $latestVersionRes['testflight_url'];
//                        }
//
//                    }

                    $response['testflight']['url'] = $testflightUrl ?: '';
                    $response['testflight']['leftDays'] = $leftDays;
                    $response['testflight']['hasNewer'] = $hasNewerVersion;
                    $response['testflight']['content'] = $testflightContent;

                    return response()->json(['data' => $response, 'msg' => '登陆成功', 'code' => 200]);
                }
                return response()->json(['data' => [], 'msg' => '账号或密码错误', 'code' => 202]);
            }
            return response()->json(['data' => [], 'msg' => '账号不存在', 'code' => 202]);
        }
        return response()->json(['data' => [], 'msg' => '登陆失败，请重试！', 'code' => 202]);
    }

    public function logout(Request $request){        //清空用户session
        if ($request->session()->has('user')) {
            $request->session()->forget('user');
        }

        return response()->json(['data' => [],'msg' => '登出成功', 'code' => 200]);
    }


    public function queryUserVip(Request $request){
        if($request->filled('device_code')){
            $now = time();
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
            $userInfo = [];
            if($request->filled('email'))
                $userInfo = Appuser::where('email', $request->input('email'))->first();

            if(empty($deviceInfo) && empty($userInfo))
                return response()->json(['msg' => '用户不存在', 'data' => '', 'code' => 202]);

            //新版testFlight版本信息
            $testFlight = [];
            $testflightUrl = SystemSetting::getValueByName('seeTestFlightUrl') ? : '';
            $testflightContent = '';
            $hasNewerVersion = 0;
            if($request->filled('version') && $request->input('version') != 3){
//                $cacheVersion = 0;
//                if(Cache::has($request->input('device_code'))){
//                    $cacheVersion = Cache::get($request->input('device_code'));
//                }
                $latestVersionRes = SeeVersion::orderBy('app_version', 'DESC')->first();
                if(($request->input('version', 0) < $latestVersionRes['app_version'])){
                    $hasNewerVersion = 1;
                    $testflightContent = $latestVersionRes['content'];
//                    $expiresAt = Carbon::now()->addHours(12);
//                    Cache::put($request->input('device_code'), $latestVersionRes['app_version'], $expiresAt);
                }
            }
            $testFlight['url'] = $testflightUrl;
            $testFlight['hasNewer'] = $hasNewerVersion;
            $testFlight['content'] = $testflightContent;

            //展示公告
            if($request->input('version') != 3)
                $announcement = Announcement::find(4);
            else
                $announcement = Announcement::find(3);
            $userAnnouncement['online'] = 0;
            $userAnnouncement['content'] = $userAnnouncement['redirect_url'] = '';
            if($announcement){
                $userAnnouncement['online'] = $announcement['online'] ? 1 : 0;
                $userAnnouncement['content'] = $announcement['content'] ? : '';
                $userAnnouncement['redirect_url'] = $announcement['redirect_url'] ? : '';
                if($announcement['id'] == 4){
                    if(empty($deviceInfo['uid'])){
                        $token = md5($deviceInfo['uuid'] . 'seedevicezhuanyi');
                        $userAnnouncement['redirect_url'] = action('AppusersController@seeDeviceZhuanyiPage', ['uuid' => $deviceInfo['uuid'], 'token' => $token]) ? : '';
                    }else{
                        $userAnnouncement['redirect_url'] = action('AppusersController@seeAccountZhuanyiPage', ['uuid' => $deviceInfo['uuid']]) ? : '';
                    }
                }
            }

            $totalExpiredTime = 0;
            if($deviceInfo){
                $totalExpiredTime = $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0;
            }
            if($userInfo)
                $totalExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] - $now : 0;

            $isSupportPay = 0;
            if(in_array($deviceInfo['uuid'], ['1023492']))
                $isSupportPay = 1;

            return response()->json(['msg' => '查询成功', 'data' => ['vipExpired' => $totalExpiredTime, 'testflight' => $testFlight, 'announcement' => $userAnnouncement, 'isSupportPay' => $isSupportPay], 'code' => 200]);
        }
        return response()->json(['msg' => '查询失败，参数异常', 'data' => '', 'code' => 202]);
    }

    public function addVip(Request $request){
        return response()->json(['msg' => 'OK', 'data' => '', 'code' => 200]);
        if($request->filled('uuid') && $request->filled('days') && $request->filled('user_uuid')){
            if($request->input('uuid') == '1000047'){
                $device = Device::where('uuid', $request->input('user_uuid'))->first();
                if($device){
                    $user = Appuser::find($device['uid']);
                    if($user){
                        $now = time();
                        $vipExpireAt = $user->vip_expired > $now ? $user->vip_expired : $now;
                        $freeLeft = 0;
                        if($device->free_vip_expired > $now && $vipExpireAt == $now)
                            $freeLeft = $device->free_vip_expired - $now;

                        $user->vip_expired = strtotime('+' . $request->input('days', 0) . ' days', $vipExpireAt) + $freeLeft;
                        $user->save();
                        $dateStr = date('Y-m-d', $user['vip_expired']);
                        return response()->json(['msg' => '增加vip时长成功，最新到期时间: ' . $dateStr, 'data' => '', 'code' => 200]);
                    }
                    return response()->json(['msg' => '该用户尚未注册账号', 'data' => '', 'code' => 202]);
                }
                return response()->json(['msg' => 'uuid不存在', 'data' => '', 'code' => 202]);
            }
            return response()->json(['msg' => '无权限', 'data' => '', 'code' => 202]);
        }
        return response()->json(['msg' => '参数错误', 'data' => '', 'code' => 202]);
    }

    public function servers(Request $request){
        $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);
        return response()->json(['msg' => '获取成功', 'data' => $response, 'code' => 200]);
    }


    public function accountServerList(Request $request){
        if($request->filled('device_code')){
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->where('status', 1)->first();
            if(empty($deviceInfo))
                return response()->json(['msg' => '设备不存在', 'data' => '', 'code' => 202]);

            $servers = AccountServers::get(['name', 'address', 'port', 'password', 'secret']);
            $userInfo = $deviceInfo['uid'] ? Appuser::find($deviceInfo['uid']) : [];
            $serversRes = [];
            if($servers){
                $aesRes = new AesController();
                $serversRes = $servers->each(function ($item, $key) use ($userInfo, $aesRes) {
                    $now = time();
                    if($userInfo['vip_expired'] > $now){
                        $item['password'] = $aesRes->encrypt($item['password']);
                    }else{
                        $item['password'] = $aesRes->encrypt('仅限会员使用');
                    }
                });
            }
            return response()->json(['msg' => '获取成功', 'data' => $serversRes, 'code' => 200]);
        }
        return response()->json(['msg' => '获取失败，参数错误', 'data' => '', 'code' => 202]);
    }

    public function appAccountServerList(Request $request){
        if($request->filled('device_code') && $request->filled('appname')){
            $now = time();
            $appname = $request->input('appname', 'see');
            $deviceInfo = '';
            $isvip = 0;
            switch ($appname) {
                case 'see':
                    $deviceInfo = Device::where('device_code', $request->input('device_code'))->where('status', 1)->first();
                    $userInfo = $deviceInfo['uid'] ? Appuser::find($deviceInfo['uid']) : [];
                    $isvip = ($userInfo && $userInfo['vip_expired'] > $now) ? 1 : 0;
                    break;
                case 'feng':
                    $deviceInfo = FengDevice::where('device_code', $request->input('device_code'))->where('status', 1)->first();
                    $userInfo = $deviceInfo['uid'] ? FengUser::find($deviceInfo['uid']) : [];
                    $isvip = ($userInfo && $userInfo['vip_expireat'] > $now) ? 1 : 0;
                    break;
                case 'flower':
                    $deviceInfo = FlowerUser::where('code', $request->input('device_code'))->first();
                    $totalVipExpired = $deviceInfo['vip_expireat'] > $now ? $deviceInfo['vip_expireat'] - $now : 0;
                    $vipType = 0;
                    if($deviceInfo['paid_vip_expireat'] > $now){
                        $vipType = 2;
                    }else{
                        if($totalVipExpired > 0)
                            $vipType = 1;
                    }
                    $vipType = $deviceInfo['is_permanent_vip'] ? 3 : $vipType;
                    $isvip = $vipType >= 2 ? 1 : 0;
                    break;
            }
            if(empty($deviceInfo))
                return response()->json(['msg' => '设备不存在', 'data' => '', 'code' => 202]);

            $servers = AccountServers::get(['name', 'address', 'port', 'password', 'secret']);

            $serversRes = [];
            if($servers){
                $aesRes = new AesController();
                $serversRes = $servers->each(function ($item, $key) use ($isvip, $aesRes) {
                    if($isvip){
                        $item['password'] = $aesRes->encrypt($item['password']);
                    }else{
                        $item['password'] = $aesRes->encrypt('仅限会员使用');
                    }
                });
            }
            return response()->json(['msg' => '获取成功', 'data' => $serversRes, 'code' => 200]);
        }
        return response()->json(['msg' => '获取失败，参数错误', 'data' => '', 'code' => 202]);
    }

    public function serverList(Request $request){
        if($request->filled('device_code')){
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->where('status', 1)->first();
            if(empty($deviceInfo))
                return response()->json(['msg' => '设备不存在', 'data' => '', 'code' => 202]);

            $servers = VipServer::get(['name', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd']);
//            $userInfo = $deviceInfo['uid'] ? Appuser::find($deviceInfo['uid']) : [];
            $serversRes = [];
            if($servers){
//                $now = time();
                $aesRes = new AesController();
//                $vipExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] : $now;
//                $totalExpiredTime = $deviceInfo['free_vip_expired'] > $vipExpiredTime ? $deviceInfo['free_vip_expired'] - $now : $vipExpiredTime - $now;

                $serversRes = $servers->each(function ($item, $key) use ($aesRes) {
                    $item['port'] = random_int($item['start_port'], $item['end_port']);
                    unset($item['start_port'], $item['end_port']);
                    $item['server_pwd'] = $aesRes->encrypt($item['server_pwd']);
//                    if($totalExpiredTime > 0){
//                        $item['server_pwd'] = $aesRes->encrypt($item['server_pwd']);
//                    }else{
//                        $item['server_pwd'] = $aesRes->encrypt('仅限会员使用');
//                    }
                });
            }
            return response()->json(['msg' => '获取成功', 'data' => $serversRes, 'code' => 200]);
        }
        return response()->json(['msg' => '获取失败，参数错误', 'data' => '', 'code' => 202]);
    }

    public function newServerList(Request $request){
        if($request->filled('device_code')){
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->where('status', 1)->first();
            if(empty($deviceInfo))
                return response()->json(['msg' => '设备不存在', 'data' => '', 'code' => 202]);

            $currentIP = $request->input('ip', '');
            $currentServerGid = 0;
            $currentServer = [];
            if($currentIP){
                $currentServer = ServersList::where('address', $currentIP)->first();
                $currentServerGid = $currentServer ? $currentServer['server_gid'] : 0;
            }
            //get all server gids
//            $listIds = ServersList::where('server_gid', '!=', $currentServerGid)->inRandomOrder()->pluck('id', 'server_gid');
            $query = ServersList::groupBy('server_gid');
            if($currentServerGid)
                $query = $query->where('server_gid', '!=', $currentServerGid);

            $serverListGIds = $query->pluck('server_gid');
            $sids = [];
            foreach ($serverListGIds as $key => $gid) {
                $serverGroupRate = ServersList::where('server_gid', $gid)->sum('random_rate');
                $serverGroup = ServersList::where('server_gid', $gid)->orderBy('id')->get(['random_rate', 'id']);
                if(empty($serverGroup))
                    continue;

                $randomValue = random_int(1, $serverGroupRate);
                $total = 0;
                foreach ($serverGroup as $k => $server) {
                    $total += $server['random_rate'];
                    if($total >= $randomValue){
                        $sids[] = $server['id'];
                        break;
                    }
                }
            }
            if($currentServer)
                array_push($sids, $currentServer['id']);

            $servers = ServersList::whereIn('id', $sids)->get(['name', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd']);

            $serversRes = [];
            if($servers){
                $aesRes = new AesController();
                $serversRes = $servers->each(function ($item, $key) use ($aesRes) {
                    $item['port'] = random_int($item['start_port'], $item['end_port']);
                    unset($item['start_port'], $item['end_port']);
                    $item['server_pwd'] = $aesRes->encrypt($item['server_pwd']);
                });
            }
            return response()->json(['msg' => '获取成功', 'data' => $serversRes, 'code' => 200]);
        }
        return response()->json(['msg' => '获取失败，参数错误', 'data' => '', 'code' => 202]);
    }


    public function appServerList(Request $request){
        if($request->filled('device_code')){
            $now = time();
            $appname = $request->filled('appname') ? $request->input('appname') : 'see';
            $deviceInfo = '';
            switch ($appname) {
                case 'see':
                    $deviceInfo = Device::where('device_code', $request->input('device_code'))->where('status', 1)->first();
                    break;
                case 'feng':
                    $deviceInfo = FengDevice::where('device_code', $request->input('device_code'))->where('status', 1)->first();
                    break;
                case 'flower':
                    $deviceInfo = FlowerUser::where('code', $request->input('device_code'))->first();
                    break;
            }
            if(empty($deviceInfo))
                return response()->json(['msg' => '设备不存在', 'data' => '', 'code' => 202]);

            if($appname === 'flower' && $deviceInfo['paid_vip_expireat'] <= $now && $deviceInfo['is_permanent_vip'] != 1){
                $servers = FlowerAdServers::get(['name', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd']);
            }else {
                $currentIP = $request->input('ip', '');
                $currentServerGid = 0;
                $currentServer = [];
                if ($currentIP) {
                    $currentServer = AppServersList::where('address', $currentIP)->where(function ($query) use($appname) {
                        $query->where('appname', $appname)
                            ->orWhere('appname', '');
                    })->first();
                    $currentServerGid = $currentServer ? $currentServer['server_gid'] : 0;
                }
                //get all server gids
//            $listIds = AppServersList::where('server_gid', '!=', $currentServerGid)->inRandomOrder()->pluck('id', 'server_gid');
                $query = AppServersList::where(function ($query) use($appname) {
                    $query->where('appname', $appname)
                        ->orWhere('appname', '');
                })->groupBy('server_gid');
                if ($currentServerGid)
                    $query = $query->where('server_gid', '!=', $currentServerGid);

                $serverListGIds = $query->pluck('server_gid');
                $sids = [];
                foreach ($serverListGIds as $key => $gid) {
                    $serverGroupRate = AppServersList::where(function ($query) use($appname) {
                        $query->where('appname', $appname)
                            ->orWhere('appname', '');
                    })->where('server_gid', $gid)->sum('random_rate');
                    $serverGroup = AppServersList::where(function ($query) use($appname) {
                        $query->where('appname', $appname)
                            ->orWhere('appname', '');
                    })->where('server_gid', $gid)->orderBy('id')->get(['random_rate', 'id']);
                    if (empty($serverGroup))
                        continue;

                    $randomValue = random_int(1, $serverGroupRate);
                    $total = 0;
                    foreach ($serverGroup as $k => $server) {
                        $total += $server['random_rate'];
                        if ($total >= $randomValue) {
                            $sids[] = $server['id'];
                            break;
                        }
                    }
                }
                if ($currentServer)
                    array_push($sids, $currentServer['id']);

                $servers = AppServersList::whereIn('id', $sids)->get(['name', 'address', 'icon', 'type', 'start_port', 'end_port', 'encrypt_type', 'server_pwd', 'server_gid']);
            }

            $serversRes = [];
            if($servers){
                $aesRes = new AesController();
                $serversRes = $servers->each(function ($item, $key) use ($aesRes) {
                    $item['port'] = random_int($item['start_port'], $item['end_port']);
                    unset($item['start_port'], $item['end_port']);
                    $item['server_pwd'] = $aesRes->encrypt($item['server_pwd']);
                });
            }
            return response()->json(['msg' => '获取成功', 'data' => $serversRes, 'code' => 200]);
        }
        return response()->json(['msg' => '获取失败，参数错误', 'data' => '', 'code' => 202]);
    }


    //转移小花页面
    public function setFlowerVip($token = ''){
        if($token && $token === 'hisuPbRyf4gnXtj3olQaAIK1VdUHB6rF'){
            return view('set-vip', ['token' => $token]);
        }
        return view('error', ['errorMsg' => '链接异常']);
    }

    public function setFlowerVipApi(Request $request){
        if($request->filled('uuid') && $request->filled('email') && $request->filled('token') && $request->filled('viptime') && $request->filled('adminpass')){
            if($request->input('token') !== 'hisuPbRyf4gnXtj3olQaAIK1VdUHB6rF')
                return response()->json(['msg' => '页面链接异常', 'data' => '', 'code' => 202]);

            if(!in_array($request->input('adminpass', 0), [1234, 8888]))
                return response()->json(['msg' => '管理员密码错误', 'data' => '', 'code' => 202]);

            $email = $request->input('email', '');
            if(empty($email))
                return response()->json(['msg' => 'see邮箱账号不能为空', 'data' => '', 'code' => 202]);
            $seeUser = Appuser::where('email', trim($email))->first();
            if(empty($seeUser))
                return response()->json(['msg' => '该邮箱尚未注册see账户，请找用户确认', 'data' => '', 'code' => 202]);

            $uuid = $request->input('uuid', '');
            if($request->input('adminpass', 0) === 1234){
                if(empty($uuid))
                    return response()->json(['msg' => '小花uuid不能为空', 'data' => '', 'code' => 202]);
            }

            $now = time();
            $viptime = $request->input('viptime', 0);
            if($viptime == 540 || ($viptime >= 1 && $viptime <= 30)){
                if($viptime == 540){
                    $flowerUser = FlowerUsers::where('uuid', $request->input('uuid'))->where('is_permanent_vip', 1)->first();
                    $errorMsg = '该用户不是永久VIP，不可开通540天';
                }
                if($viptime >= 1 && $viptime <= 30){
                    $flowerUser = FlowerUsers::where('uuid', $request->input('uuid'))->first();
                    $errorMsg = '该用户不存在，不可转移VIP';
                }
                if($uuid){
                    if(empty($flowerUser))
                        return response()->json(['msg' => $errorMsg, 'data' => '', 'code' => 202]);

                    if ($flowerUser['processed'] === 1)
                        return response()->json(['msg' => '该用户已转移过', 'data' => '', 'code' => 202]);

                    $flowerUser->processed = 1;
                    $flowerUser->save();
                }
                $start = $seeUser['vip_expired'] > $now ? $seeUser['vip_expired'] : $now;
                $seeUser->vip_expired = $start + $viptime * 24 * 3600;
                $seeUser->save();
                //记录开通日志
                FlowerVipSetLogs::create([
                    'admin_name' => $request->input('adminpass', 0) === 1234 ? '代付' : '机子毛',
                    'flower_uuid' => $uuid,
                    'see_email' => $email,
                    'viptime' => $viptime
                ]);
                return response()->json(['msg' => '转移成功', 'data' => '', 'code' => 200]);
            }
        }
        return response()->json(['msg' => '参数写错了吧，再看看', 'data' => '', 'code' => 202]);
    }

    public function queryFlowerVip(Request $request)
    {
        if($request->filled('uuid') && $request->filled('token')){
            if($request->input('token') !== 'hisuPbRyf4gnXtj3olQaAIK1VdUHB6rF')
                return response()->json(['msg' => '页面链接异常', 'data' => '', 'code' => 202]);

            $uuid = $request->input('uuid', '');
            if(empty($uuid))
                return response()->json(['msg' => '小花uuid不能为空', 'data' => '', 'code' => 202]);

            $flowerUser = FlowerUsers::where('uuid', $uuid)->first();
            if(empty($flowerUser))
                return response()->json(['msg' => '会员不存在', 'data' => '', 'code' => 202]);

            $msg = $uuid;
            if($flowerUser['is_permanent_vip'] === 1)
                $msg = '永久VIP，';

            $now = time();
            if($flowerUser['paid_vip_expireat'] > $now){
                $viptime = round(($flowerUser['paid_vip_expireat']- $now) / (24 * 3600));
                $msg = '付费VIP，剩余' . $viptime . '天，';
            }

            if($flowerUser['free_expireat'] > $now){
                $viptime = round(($flowerUser['free_expireat']- $now) / (24 * 3600));
                $msg = '广告VIP，剩余' . $viptime . '天，';
            }

            $msg .= '该会员 未转移';
            if($flowerUser['processed'])
                $msg .= '该会员 已转移';

            return response()->json(['msg' => $msg, 'data' => '', 'code' => 200]);
        }
        return response()->json(['msg' => '参数写错了吧，再看看', 'data' => '', 'code' => 202]);
    }

    public function seeAccountZhuanyiPage(string $uuid)
    {
        $device = Device::where('uuid', $uuid)->first();
        $user = $device ? Appuser::find($device['uid']) : [];
        if(empty($user))
            return '';

        return view('account-zhuanyi', ['email' => $user['email']]);
    }

    public function seeDeviceZhuanyiPage(string $uuid, string $token)
    {
        if(empty($uuid) || empty($token))
            return '';

        $device = Device::where('uuid', $uuid)->first();
        if(empty($device) || ($device && $device['uid']))
            return '';

        $now = time();
        $days = $device['free_vip_expired'] > $now ? round(($device['free_vip_expired'] - $now) / (24 * 3600)) : 0;
        return view('device-zhuanyi', ['uuid' => $uuid, 'token' => $token, 'time' => $days]);
    }

    public function seeDeviceZhuanyiApi(string $email, string $uuid, string $token)
    {
        DB::beginTransaction();
        try {
            if(empty($email) || empty($uuid) || empty($token))
                throw new \Exception('请输入正确的邮箱账号');

            $key = $uuid . 'seedevicezhuanyi';
            $localToken = md5($key);
            if($localToken !== $token)
                throw new \Exception('参数错误，请刷新页面重试');

            $oldDevice = Device::where('uuid', $uuid)->first();
            $user = Appuser::where('email', $email)->first();
            if(empty($oldDevice) || empty($user))
                throw new \Exception('会员信息查询失败，请稍后重试');

            if($oldDevice['transfered'])
                throw new \Exception('您已完成转移，无需重复操作');

            $now = time();
            $flowerUser = FlowerTransferLogs::where('device_code', $oldDevice['device_code'])->first();
            $isPermanentVip = $flowerUser && $flowerUser['vip_type'] === 'permanent-vip' ? 1 : 0;
            $leftTime = $oldDevice['free_vip_expired'] - $now > 0 ? $oldDevice['free_vip_expired'] - $now : 0;
            $currentTime = $user->vip_expired > $now ? $user->vip_expired : $now;
            $user->vip_expired = $currentTime + $leftTime;
            $user->is_permanent_vip = $isPermanentVip;
            $user->save();
            $oldDevice->transfered = 1;
            $oldDevice->transfered_time = $now;
            $oldDevice->save();
            DB::commit();
            return response()->json(['msg' => '新版SEE VIP 到期时间为：' . date('Y-m-d H:i', $user->vip_expired), 'data' => $email, 'code' => 200]);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage(), 'data' => '', 'code' => 202]);
        }
    }

}
