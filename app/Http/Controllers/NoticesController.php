<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NoticesController extends Controller
{
    public function detail(Request $request){
        if($request->filled('version')){

        }
        return view('notice');
    }
}
