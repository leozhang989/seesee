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

class RegisterController extends Controller
{
    public function register(Request $request){
        if($request->filled('email') && $request->filled('password') && $request->filled('device_code')) {
            $isExisted = Appuser::where('email', $request->input('email'))->first(['id']);
            if ($isExisted)
                return response()->json(['data' => [], 'msg' => '该邮箱已注册过，请直接登录', 'code' => 202]);

            $deviceRes = Device::where('device_code', $request->input('device_code'))->first();
            if(empty($deviceRes))
                return response()->json(['data' => [], 'msg' => '设备无效', 'code' => 202]);

            $now = time();
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
                'vip_expired' => 0,
                'vip_left_time' => 0
            ];
            $userInfo = Appuser::create($insertData);
            if ($userInfo) {
                //update device uid
                $deviceRes->uid = $userInfo['id'];
                $deviceRes->save();

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
                $testflightContent = $testflightUrl = '';
                if ($request->filled('version')) {
                    $appVersions = AppVersion::where('online', 1)->orderBy('id', 'DESC')->pluck('app_version')->toArray();
                    $latestVersionRes = AppVersion::where('online', 1)->orderBy('id', 'DESC')->first();
                    if (!in_array($request->input('version'), $appVersions)) {
                        $latestVersionRes = AppVersion::create([
                            'app_version' => $request->input('version'),
                            'content' => '',
                            'testflight_url' => SystemSetting::getValueByName('testflightUrl') ?: '',
                            'expired_date' => $today + 90 * 24 * 3600,
                            'online' => 0
                        ]);
                    }

                    $diffDateInt = $latestVersionRes['expired_date'] - $today;
                    $leftDays = floor($diffDateInt / (3600 * 24));
                    $testflightContent = $latestVersionRes['content'];
                    $testflightUrl = $latestVersionRes['testflight_url'];
                    if ($latestVersionRes['app_version'] != $request->input('version'))
                        $hasNewerVersion = 1;
                }

                $response['testflight']['url'] = $testflightUrl ?: '';
                $response['testflight']['leftDays'] = $leftDays;
                $response['testflight']['hasNewer'] = $hasNewerVersion;
                $response['testflight']['content'] = $testflightContent;

                return response()->json(['data' => $response, 'msg' => '注册成功', 'code' => 200]);
            }
        }
        return response()->json(['data' => [], 'msg' => '注册失败，请重试！', 'code' => 202]);
    }
}
