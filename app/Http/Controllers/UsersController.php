<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
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
            'testflght' => [
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

    public function getUserInfo(Request $request){

        return response()->json(['msg' => 'success', 'data' => [], 'code' => 200]);
    }
}
