<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\AppVersion;
use App\Models\Device;
use App\Models\devicesUuidRelations;
use App\Models\FlowerTransferLogs;
use App\Models\Notice;
use App\Models\NoticeLog;
use App\Models\Server;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterController extends Controller
{
    public function register(Request $request){
        return response()->json(['data' => [], 'msg' => '系统维护需要半小时，暂停注册，请稍后再试！', 'code' => 202]);

        if($request->filled('email') && $request->filled('password') && $request->filled('device_code')) {
            //check email
            if(!filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
                return response()->json(['data' => [], 'msg' => '邮箱格式错误，请输入正确的邮箱', 'code' => 202]);

            $isExisted = Appuser::where('email', $request->input('email'))->first(['id']);
            if ($isExisted)
                return response()->json(['data' => [], 'msg' => '该邮箱已注册过，请直接登录', 'code' => 202]);

//            $deviceRes = Device::where('device_code', $request->input('device_code'))->first();
//            if(empty($deviceRes))
//                return response()->json(['data' => [], 'msg' => '设备无效', 'code' => 202]);

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

            //小花永久转移会员无需注册
//            $transferUser = FlowerTransferLogs::where('device_code', $request->input('device_code'))->where('vip_type', 'permanent-vip')->first();
//            if($transferUser)
//                return response()->json(['data' => [], 'msg' => '永久会员仅限一台设备使用，暂不支持注册账号', 'code' => 202]);

//            $today = strtotime(date('Y-m-d', $now));
            //首次注册时分配广告会员分组
//            $groupInfo =  '';
            //没有分组则创建分组
//            if(empty($groupInfo)){
//                $groupInfo = AdGroup::create([
//                    'name' => '',
//                    'max_device' => SystemSetting::getValueByName('feng_adVIPAmountPerGroup', 3000),
//                    'used' => 0,
//                    'is_full' => 0,
//                ]);
//            }
            $insertData = [
                'name' => '',
                'gid' => 0,
                'email' => $request->input('email'),
                'password' => MD5($request->input('password')),
                'phone' => '',
                'free_vip_expired' => $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : 0,
                'vip_expired' => $deviceRes['free_vip_expired'] > $now ? $deviceRes['free_vip_expired'] : 0,
                'vip_left_time' => 0
            ];
            $userInfo = Appuser::create($insertData);
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

//                if ($request->filled('version')) {
//                    $appVersions = AppVersion::where('online', 1)->orderBy('id', 'DESC')->pluck('app_version')->toArray();
//                    $latestVersionRes = AppVersion::where('online', 1)->orderBy('id', 'DESC')->first();
//                    $userVersion = AppVersion::where('app_version', $request->input('version'))->first();
//                    if (!in_array($request->input('version'), $appVersions)) {
//                        $latestVersionRes = AppVersion::create([
//                            'app_version' => $request->input('version'),
//                            'content' => '',
//                            'testflight_url' => SystemSetting::getValueByName('testflightUrl') ?: '',
//                            'expired_date' => $today + 90 * 24 * 3600,
//                            'online' => 0
//                        ]);
//                    }else{
//                        if($userVersion['online'] === 0)
//                            $latestVersionRes = $userVersion;
//                    }
//
//                    $diffDateInt = $userVersion['expired_date'] - $today > 0 ? $userVersion['expired_date'] - $today : 0;
//                    $leftDays = floor($diffDateInt / (3600 * 24));
//                    $testflightContent = $userVersion['content'];
//                    $testflightUrl = $userVersion['testflight_url'];
//                    if ($latestVersionRes['app_version'] != $request->input('version')){
//                        $hasNewerVersion = 1;
//                        $testflightContent = $latestVersionRes['content'];
//                        $testflightUrl = $latestVersionRes['testflight_url'];
//                    }
//                }

                $response['testflight']['url'] = $testflightUrl ?: '';
                $response['testflight']['leftDays'] = $leftDays;
                $response['testflight']['hasNewer'] = $hasNewerVersion;
                $response['testflight']['content'] = $testflightContent;

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
        $lastUser = DB::table('devices')
            ->latest()
            ->first();
        $lastUuid = $lastUser && $lastUser->uuid ? $lastUser->uuid : '1000011';
        $length = strlen($lastUuid) - 1;
        $uuid = substr($lastUuid, 0, $length) + 1 . random_int(0, 9);
        return $uuid;
    }
}
