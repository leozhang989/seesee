<?php

namespace App\Http\Controllers;


use App\Models\SystemSetting;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function dengDownload(Request $request){
        $dengTestFlightUrl = SystemSetting::getValueByName('dengTestFlightUrl') ? : '';
        return view('deng-download', ['testFlightUrl' => $dengTestFlightUrl]);
    }


}
