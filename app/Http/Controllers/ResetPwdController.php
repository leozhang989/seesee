<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SendemailController as SendEMail;
use App\Models\Appuser;
use App\Models\ResetEmailLog;
use Illuminate\Http\Request;

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

}
