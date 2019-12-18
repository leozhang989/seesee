<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getUserInfo(Request $request){
        $data = [
            'userInfo' => [],
            'servers' => []
        ];
        return response()->json(['msg' => 'success', 'data' => $data, 'code' => 200]);
    }
}
