<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SendemailController as SendEMail;
use App\Models\Appuser;
use App\Models\Device;
use App\Models\FengUser;
use App\Models\FlowerUser;
use App\Models\ResetEmailLog;
use Illuminate\Http\Request;
use App\Models\GroupGiftlLog;
use App\Models\SystemSetting;
use App\Models\FengDevice;

class ResetPwdController extends Controller
{
    public function resetPassword(Request $request){
        if($request->filled('email')) {
            $user = Appuser::where('email', $request->input('email'))->first();
            if(empty($user))
                return response()->json(['msg' => '此邮箱尚未注册！', 'data' => '', 'code' => 200]);

            $now = time();
            $lastLog = ResetEmailLog::where('email', $request->input('email'))->where('status', 0)->first(['send_time']);
            if(($lastLog['send_time'] + 30) >= $now)
                return response()->json(['msg' => '操作过于频繁，请稍后重试！', 'data' => [], 'code' => 202]);

            $tokenString = generateRandomCode(32);
            $resetUrl = action('ResetPwdController@resetPage', ['token' => $tokenString, 'email' => $request->input('email')]);
            $insertData = ['email' => $request->input('email'), 'reset_token' => $tokenString, 'reset_url' => $resetUrl, 'status' => 0, 'send_time' => $now, 'valid_time' => $now + 2 * 3600];
            $res = ResetEmailLog::create($insertData);
            if ($res) {
                $to = $request->input('email');
                $subject = '风速帐号密码重置';
                $status = SendEMail::send($to, $subject, $resetUrl);
                if ($status)
                    return response()->json(['msg' => '发送成功', 'data' => $res['id'], 'code' => 200]);
            }
        }
        return response()->json(['msg' => '发送失败', 'data' => [], 'code' => 202]);
    }


    public function resetPage(Request $request, $token = '', $email = ''){
        if($token && $email){
            //check if token valid
            $now = time();
            $emailLog = ResetEmailLog::where('reset_token', $token)->where('email', $email)->where('valid_time', '>=', $now)->where('status', 0)->first();
            if(empty($emailLog))
                return view('error', ['errorMsg' => '重置链接已无效']);

            return view('reset-page', ['resetToken' => $token, 'email' => $email]);
        }
        return view('error', ['errorMsg' => '链接异常']);
    }

    public function newPassword(Request $request){
        if($request->filled('new-pwd') && $request->filled('new-pwd-confirm') && $request->filled('reset-token') && $request->filled('email')){
            if($request->input('new-pwd') !== $request->input('new-pwd-confirm')){
                return view('reset-msg', ['msg' => '两次输入的密码不一致，请重新输入']);
//                return response()->json(['msg' => '两次输入的密码不一致，请重新输入', 'data' => '', 'code' => 202]);
            }
            $token = $request->input('reset-token');
            $email = $request->input('email');
            $now = time();
            $emailLog = ResetEmailLog::where('reset_token', $token)->where('email', $email)->where('valid_time', '>=', $now)->where('status', 0)->first();
            if(empty($emailLog)){
                return view('reset-msg', ['msg' => '此链接已无效，请重新发送重置链接']);
//                return response()->json(['msg' => '此链接已无效，请重新发送重置链接', 'data' => '', 'code' => 202]);
            }

            $userInfo = Appuser::where('email', $email)->first();
            if($userInfo){
                $userInfo->password = MD5($request->input('new-pwd'));
                $userInfo->save();
                //无效重置链接操作
                $emailLog->status = 1;
                $emailLog->save();
                return view('reset-msg', ['msg' => '密码已重置']);
//                return response()->json(['msg' => '修改成功', 'data' => '', 'code' => 200]);
            }
        }
        return view('reset-msg', ['msg' => '请填写新密码和确认新密码']);
//        return response()->json(['msg' => '修改失败', 'data' => '', 'code' => 202]);
    }

    public function groupGiftPage(Request $request, $token = ''){
        return view('group-gift', ['token' => $token]);
    }

    public function getGroupGift(Request $request){
        if($request->filled('uuid') || $request->filled('token')){
            $tokens = ['sYBdOvDtQfR0wSCN'];
            $token = $request->input('token', '');
            if(!in_array($token, $tokens))
                return response()->json(['msg' => '链接已失效！', 'data' => '', 'code' => 202]);

            $userUuid = $request->input('uuid');
            $giftRes = GroupGiftlLog::where('user_uuid', $userUuid)->first();
            if($giftRes)
                return response()->json(['msg' => '您已领取过，请勿重复领取！', 'data' => '', 'code' => 202]);

            //add vip time
            $length = strlen($userUuid);
            $now = time();
            $userDeviceCodes = [];
            if(in_array($length, [7, 8, 10])){
                $groupGiftDays = SystemSetting::getValueByName('GroupGiftDays') ? : 0;
                if ($length == 7) {
                    $appName = 'see';
                    $userDeviceCodes = Device::where('uuid', $userUuid)->pluck('device_code');
                    $recordCodes = GroupGiftlLog::where('app_name', $appName)->pluck('device_code');
                    foreach ($userDeviceCodes as $code) {
                        foreach ($recordCodes as $rcodes) {
                            $rcodesAry = $rcodes ? json_decode($rcodes, TRUE) : [];
                            if(in_array($code, $rcodesAry))
                                return response()->json(['msg' => '您已领取过，请勿重复领取！', 'data' => '', 'code' => 202]);
                        }
                    }
                    $device = Device::where('uuid', $userUuid)->where('status', 1)->first();
                    $user = $device ? Appuser::find($device['uid']) : '';
                    if(empty($user))
                        return response()->json(['msg' => '用户uuid不存在', 'data' => '', 'code' => 202]);
                    $vipExpireAt = $user->vip_expired > $now ? $user->vip_expired : $now;
                    $user->vip_expired = strtotime('+' . $groupGiftDays . ' days', $vipExpireAt);
                }
                if ($length == 8) {
                    return response()->json(['msg' => '福利领取失败', 'data' => '', 'code' => 202]);

//                    $appName = 'flower';
//                    $user = FlowerUser::where('uuid', $userUuid)->first();
//                    if(empty($user))
//                        return response()->json(['msg' => '用户uuid不存在', 'data' => '', 'code' => 202]);
//                    if($user->is_permanent_vip == 1)
//                        return response()->json(['msg' => '您已开通永久VIP，无需领取福利天数', 'data' => '', 'code' => 202]);
//
//                    if($user->paid_vip_expireat > $now)
//                        $user->paid_vip_expireat = strtotime('+' . $groupGiftDays . ' days', $user->paid_vip_expireat);
//                    else
//                        $user->free_expireat = strtotime('+' . $groupGiftDays . ' days', $user->free_expireat);
                }
                if ($length == 10) {
                    $appName = 'feng';
                    $user = FengUser::where('uuid', $userUuid)->first();
                    $userDeviceCodes = FengDevice::where('uid', $user['uid'])->pluck('device_code');
                    $recordCodes = GroupGiftlLog::where('app_name', $appName)->pluck('device_code');
                    foreach ($userDeviceCodes as $code) {
                        foreach ($recordCodes as $rcodes) {
                            $rcodesAry = $rcodes ? json_decode($rcodes, TRUE) : [];
                            if(in_array($code, $rcodesAry))
                                return response()->json(['msg' => '您已领取过，请勿重复领取！', 'data' => '', 'code' => 202]);
                        }
                    }
                    if(empty($user))
                        return response()->json(['msg' => '用户uuid不存在', 'data' => '', 'code' => 202]);
                    if($user->vip_expireat > $now)
                        $user->vip_expireat = strtotime('+' . $groupGiftDays . ' days', $user->vip_expireat);
                    else
                        $user->ad_vip_expireat = strtotime('+' . $groupGiftDays . ' days', $user->ad_vip_expireat);
                }
                if (!$user->save())
                    return response()->json(['msg' => '福利领取失败', 'data' => '', 'code' => 202]);

                //record
                $insertData = ['user_uuid' => $userUuid, 'get_time' => $now, 'gift_days' => $groupGiftDays, 'app_name' => $appName, 'device_code' => json_encode($userDeviceCodes)];
                GroupGiftlLog::create($insertData);
                return response()->json(['msg' => '福利领取成功', 'data' => '', 'code' => 200]);
            }
        }
        return response()->json(['msg' => '请输入您正确的uuid', 'data' => '', 'code' => 202]);
    }

}
