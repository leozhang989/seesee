<?php

namespace App\Http\Controllers;

use App\Models\Appuser;
use App\Models\AppVersion;
use App\Models\Device;
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
//                if(!in_array($request->input('version'), $appVersions)){
//                    $latestVersionRes = AppVersion::create([
//                        'app_version' => $request->input('version'),
//                        'content' => '',
//                        'testflight_url' => SystemSetting::getValueByName('testflightUrl') ? : '',
//                        'expired_date' => $nowDate + 90 * 24 * 3600
//                    ]);
//                }
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

            $response['userInfo'] = [
                'uuid' => $deviceInfo['uuid'] ? : '',
//                'freeVipExpired' => 0,
                'vipExpired' => $deviceInfo['free_vip_expired'] > $now ? $deviceInfo['free_vip_expired'] - $now : 0,
                'isVip' => 1,
                'email' => '',
                'hasNewNotice' => 0,
                'noticeUrl' => SystemSetting::getValueByName('noticeUrl') ? SystemSetting::getValueByName('noticeUrl') . '/1' : ''
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



    //debug api
    public function getUserInfoTest(Request $request){
        $type = $request->input('type', 1);
        $data = [
            'userInfo' => [
                'uuid' => '123123',
                'freeVipExpired' => 0,
                'vipExpired' => 30 * 24 * 3600,
                'isVip' => 1,
                'email' => '123123@qq.com'
            ],
            'servers' => [
                [
                    'name' => '美国1',
                    'address' => '123.123.3.123',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ],
                [
                    'name' => '美国2',
                    'address' => '123.123.1.222',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ],
                [
                    'name' => '美国3',
                    'address' => '123.123.12.333',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ]
            ],
            'testflight' => [
                'url' => 'https://baidu.com',
                'leftDays' => 87,
                'hasNewer' => 1
            ]
        ];
        if($type === 1) {
            $data['userInfo']['freeVipExpired'] = 10 * 24 * 3600;
            $data['userInfo']['vipExpired'] = 0;
            $data['userInfo']['isVip'] = 0;
        }
        return response()->json(['msg' => 'success', 'data' => $data, 'code' => 200]);
    }


    public function getUserInfoTestUnsign(Request $request){
        $type = $request->input('type', 1);
        $data = [
            'userInfo' => [
                'uuid' => '123123',
                'freeVipExpired' => 0,
                'vipExpired' => 30 * 24 * 3600,
                'isVip' => 1,
                'email' => '123123@qq.com'
            ],
            'servers' => [
                [
                    'name' => '美国1',
                    'address' => '123.123.3.123',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ],
                [
                    'name' => '美国2',
                    'address' => '123.123.1.222',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ],
                [
                    'name' => '美国3',
                    'address' => '123.123.12.333',
                    'icon' => 'AmericaFlag',
                    'type' => 'vip'
                ]
            ],
            'testflight' => [
                'url' => 'https://baidu.com',
                'leftDays' => 87,
                'hasNewer' => 1
            ]
        ];
        if($type === 1) {
            $data['userInfo']['freeVipExpired'] = 10 * 24 * 3600;
            $data['userInfo']['vipExpired'] = 0;
            $data['userInfo']['isVip'] = 0;
        }
        return response()->json(['msg' => 'success', 'data' => $data, 'code' => 200]);
    }
}
