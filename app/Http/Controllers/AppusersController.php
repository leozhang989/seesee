<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Appuser;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\FengDevice;
use App\Models\FlowerAdServers;
use App\Models\FlowerUser;
use App\Models\Notice;
use App\Models\NoticeLog;
use App\Models\SeeVersion;
use App\Models\Server;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AesController;
use App\Models\AccountServers;
use App\Models\VipServer;
use App\Models\ServersList;
use App\Models\AppServersList;

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

//            if($request->filled('version')){
//                $appVersions = AppVersion::where('online', 1)->orderBy('id', 'DESC')->pluck('app_version')->toArray();
//                $latestVersionRes = AppVersion::where('online', 1)->orderBy('id', 'DESC')->first();
//                $userVersion = AppVersion::where('app_version', $request->input('version'))->first();
//                if(!in_array($request->input('version'), $appVersions)){
//                    $latestVersionRes = AppVersion::create([
//                        'app_version' => $request->input('version'),
//                        'content' => '',
//                        'testflight_url' => SystemSetting::getValueByName('testflightUrl') ? : '',
//                        'expired_date' => $nowDate + 90 * 24 * 3600,
//                        'online' => 0
//                    ]);
//                }else{
//                    if($userVersion['online'] === 0)
//                        $latestVersionRes = $userVersion;
//                }
//                $diffDateInt = $userVersion['expired_date'] - $nowDate > 0 ? $userVersion['expired_date'] - $nowDate : 0;
//                $leftDays = floor($diffDateInt / (3600 * 24));
//                $testflightContent = $userVersion['content'];
//                $testflightUrl = $userVersion['testflight_url'];
//                if ($latestVersionRes['app_version'] != $request->input('version')){
//                    $hasNewerVersion = 1;
//                    $testflightContent = $latestVersionRes['content'];
//                    $testflightUrl = $latestVersionRes['testflight_url'];
//                }
//            }

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
                $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                $newNotice = $userNoticeLog ? 0 : 1;
                $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ? : '';
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

                    $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
                    if ($deviceInfo) {
                        $deviceInfo->uid = $user['id'];
                        $deviceInfo->save();
                    } else {
                        $uuid = $this->generateUUID();
                        $freeDays = SystemSetting::getValueByName('freeDays');
                        $deviceInfo = Device::create([
                            'uuid' => $uuid,
                            'device_code' => $request->input('device_code'),
                            'is_master' => 0,
                            'status' => 1,
                            'free_vip_expired' => strtotime('+' . $freeDays . ' day'),
                            'uid' => $user['id']
                        ]);
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
                        $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                        $newNotice = $userNoticeLog ? 0 : 1;
                        $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ?: '';
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
            if($request->filled('version')){
                $cacheVersion = 0;
                if(Cache::has($request->input('device_code'))){
                    $cacheVersion = Cache::get($request->input('device_code'));
                }
                $latestVersionRes = SeeVersion::orderBy('app_version', 'DESC')->first();
                if(($request->input('version', 0) < $latestVersionRes['app_version']) && ($cacheVersion == 0 || $cacheVersion != $latestVersionRes['app_version'])){
                    $hasNewerVersion = 1;
                    $testflightContent = $latestVersionRes['content'];
                    $expiresAt = Carbon::now()->addHours(12);
                    Cache::put($request->input('device_code'), $latestVersionRes['app_version'], $expiresAt);
                }
            }
            $testFlight['url'] = $testflightUrl;
            $testFlight['hasNewer'] = $hasNewerVersion;
            $testFlight['content'] = $testflightContent;

            //展示公告
            $announcement = Announcement::where('online', 1)->orderBy('id', 'desc')->first();
            $userAnnouncement['online'] = 0;
            $userAnnouncement['content'] = $userAnnouncement['redirect_url'] = '';
            if($announcement){
                $userAnnouncement['online'] = $announcement['online'] ? 1 : 0;
                $userAnnouncement['content'] = $announcement['content'] ? : '';
                $userAnnouncement['redirect_url'] = $announcement['redirect_url'] ? : '';
            }

            $totalExpiredTime = 0;
            if($deviceInfo){
                $totalExpiredTime = $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0;
            }
            if($userInfo)
                $totalExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] - $now : 0;
            return response()->json(['msg' => '查询成功', 'data' => ['vipExpired' => $totalExpiredTime, 'testflight' => $testFlight, 'announcement' => $userAnnouncement], 'code' => 200]);
        }
        return response()->json(['msg' => '查询失败，参数异常', 'data' => '', 'code' => 202]);
    }

    public function addVip(Request $request){
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
        if($request->filled('device_code') && $request->filled('appname')){
            $now = time();
            $appname = $request->input('appname', 'see');
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

}
