<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\devicesUuidRelations;
use App\Models\Notice;
use App\Models\NoticeLog;
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
    public function login(Request $request){
        if($request->filled('password') && $request->filled('email') && $request->filled('device_code')) {
            $userExisted = Seeuser::where('email', trim($request->input('email')))->count();
            if($userExisted > 0) {
                $user = Seeuser::where('email', trim($request->input('email')))->where('password', MD5($request->input('password')))->first();
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

                    //永久会员限制一台设备
                    if($user['is_permanent_vip'] == 1){  //一定是转移过的 一定有一台设备
                        $deviceData = Device::where('uid', $user['id'])->where('transfered', 1)->orderBy('created_at', 'DESC')->first();
                        if($deviceData && $deviceData['device_code'] != $request->input('device_code', '')){
                            return response()->json(['data' => [], 'msg' => '永久用户仅限一台设备永久使用，不支持多设备同时登录', 'code' => 202]);
                        }
                    }

                    $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
                    if ($deviceInfo) {
                        $deviceInfo->uid = $user['id'];
                        $deviceInfo->device_model = trim($request->input('model', ''));
                        $deviceInfo->save();
                    } else {
                        $freeDays = SystemSetting::getValueByName('freeDays');
                        //查询关联表是否已经有老设备的关联记录
                        $deviceRes = devicesUuidRelations::where('device_code', $request->input('device_code'))->first();
                        if($deviceRes){
                            $freeVipExpired = $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : $now;
                        }else{
                            $freeVipExpired = strtotime('+' . $freeDays . ' day');
                            devicesUuidRelations::create([
                                'uuid' => $user['uuid'],
                                'device_code' => $request->input('device_code'),
                                'free_vip_expired' => $freeVipExpired,
                                'uid' => $user['id']
                            ]);
                        }
                        $deviceInfo = Device::create([
                            'uuid' => $user['uuid'],
                            'device_code' => $request->input('device_code'),
                            'is_master' => 0,
                            'status' => 1,
                            'free_vip_expired' => $freeVipExpired,
                            'uid' => $user['id'],
                            'device_model' => trim($request->input('model', ''))
                        ]);
                    }
                    unset($user['id'], $user['created_at'], $user['updated_at'], $user['name']);

                    //if has new notice now
                    $newNotice = 0;
                    $noticeUrl = '';
                    $nowDate = date('Y-m-d H:i:s', $now);
                    $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                    if ($latestNotice) {
                        $userNoticeLog = NoticeLog::where('uuid', $deviceInfo['uuid'])->where('notice_id', $latestNotice['id'])->first();
                        $newNotice = $userNoticeLog ? 0 : 1;
                        $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceInfo['uuid']]) ?: '';
                    }

                    $vipExpiredTime = $user['vip_expired'] > $now ? $user['vip_expired'] : $now;
                    $totalExpiredTime = $deviceInfo['free_vip_expired'] > $vipExpiredTime ? $deviceInfo['free_vip_expired'] - $now : $vipExpiredTime - $now;
                    $response['userInfo'] = [
                        'uuid' => $user['uuid'] ?: '',
                        'vipExpired' => $totalExpiredTime,
                        'isVip' => $totalExpiredTime > 0 ? 1 : 0,
                        'email' => trim($request->input('email')),
                        'hasNewNotice' => $newNotice,
                        'noticeUrl' => $noticeUrl,
                        'paymentUrl' => action('PayController@list', ['token' => $deviceInfo['uuid']]),
                    ];
                    $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);

                    if($request->filled('device_identifier')){
                        $deviceInfo->device_identifier = $request->input('device_identifier');
                        $deviceInfo->save();
                    }

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
            $deviceInfo = Device::where('device_code', $request->input('device_code'))->first();
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
                $latestVersionRes = SeeVersion::orderBy('app_version', 'DESC')->first();
                if (Cache::has($request->input('device_code'))) {
                    $cacheVersion = Cache::get($request->input('device_code'));
                }
                if (($request->input('version', 0) < $latestVersionRes['app_version']) && empty($cacheVersion)) {
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
            $announcement = Announcement::orderBy('id', 'DESC')->first();
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

    public function register(Request $request){
        if($request->filled('email') && $request->filled('password') && $request->filled('device_code')) {
            //check email
            if(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
                return response()->json(['data' => [], 'msg' => '邮箱格式错误，请输入正确的邮箱', 'code' => 202]);

            $isExisted = Seeuser::where('email', $request->input('email'))->first(['id']);
            if ($isExisted)
                return response()->json(['data' => [], 'msg' => '该邮箱已注册过，请直接登录', 'code' => 202]);

            //注册时写入设备
            $deviceResRela = null;
            $deviceRes = Device::where('device_code', $request->input('device_code'))->first();
            $now = time();
            if (empty($deviceRes)) {
                $freeDays = SystemSetting::getValueByName('freeDays');
                //查询关联表是否已经有老设备的关联记录
                $deviceResRela = devicesUuidRelations::where('device_code', $request->input('device_code'))->first();
                if($deviceResRela){
                    $uuid = $deviceResRela['uuid'];
                    $freeVipExpired = $deviceResRela['free_vip_expired'] > $now ? $deviceResRela['free_vip_expired'] : $now;
                }else{
                    $uuid = $this->generateUUID();
                    if(empty($uuid))
                        return response()->json(['msg' => '设备登录失败，请重试', 'data' => [], 'code' => 202]);

                    $freeVipExpired = strtotime('+' . $freeDays . ' day');
                    $deviceResRela = devicesUuidRelations::create([
                        'uuid' => $uuid,
                        'device_code' => $request->input('device_code'),
                        'free_vip_expired' => $freeVipExpired,
                        'uid' => 0
                    ]);
                }
                $deviceRes = Device::create([
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
                'uuid' => $deviceRes['uuid'] ?? ''
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
                $today = strtotime(date('Y-m-d', $now));
                $latestNotice = Notice::where('online', 1)->where('end_time', '>=', $nowDate)->orderBy('id', 'DESC')->first();
                if ($latestNotice) {
                    $userNoticeLog = NoticeLog::where('uuid', $deviceRes['uuid'])->where('notice_id', $latestNotice['id'])->first();
                    $newNotice = $userNoticeLog ? 0 : 1;
                    $noticeUrl = action('NoticesController@detail', ['id' => $latestNotice['id'], 'uuid' => $deviceRes['uuid']]) ?: '';
                }

                $vipExpiredTime = $userInfo['vip_expired'] > $now ? $userInfo['vip_expired'] : $now;
                $totalExpiredTime = $deviceRes['free_vip_expired'] > $vipExpiredTime ? $deviceRes['free_vip_expired'] - $now : $vipExpiredTime - $now;
                $response['userInfo'] = [
                    'uuid' => $deviceRes['uuid'] ?: '',
                    'vipExpired' => $totalExpiredTime,
                    'isVip' => $totalExpiredTime > 0 ? 1 : 0,
                    'email' => trim($request->input('email')),
                    'hasNewNotice' => $newNotice,
                    'noticeUrl' => $noticeUrl,
                    'paymentUrl' => action('PayController@list', ['token' => $deviceRes['uuid']]),
                ];

                $response['servers'] = Server::get(['gid', 'type', 'name', 'address', 'icon']);

                if($request->filled('device_identifier')){
                    $deviceRes->device_identifier = $request->input('device_identifier', '');
                    $deviceRes->save();
                }

                return response()->json(['data' => $response, 'msg' => '注册成功', 'code' => 200]);
            }
        }
        return response()->json(['data' => [], 'msg' => '注册失败，请重试！', 'code' => 202]);
    }

    protected function generateUUID(){
        $uuid = '';
        for ($x=0; $x<=5; $x++){
            $lastUser = DB::table('devices')
                ->latest()
                ->first();
            $lastUuid = $lastUser && $lastUser->uuid ? $lastUser->uuid : '1000011';
            $length = strlen($lastUuid) - 1;
            $uuid = substr($lastUuid, 0, $length) + 1 . random_int(0, 9);
            $ex = Device::where('uuid', $uuid)->first();
            if(empty($ex))
                break;
        }

        return $uuid;
    }

    public function updateUserUuid()
    {

    }
}
