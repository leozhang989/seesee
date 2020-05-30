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

    public function flowerDownload(Request $request){
        $flowerTestFlightUrl = SystemSetting::getValueByName('flowerTestFlightUrl') ? : '';
        return view('flower-download', ['testFlightUrl' => $flowerTestFlightUrl]);
    }

    public function seeDownload(Request $request){
        $seeTestFlightUrl = SystemSetting::getValueByName('seeTestFlightUrl') ? : '';
        return view('see-download', ['testFlightUrl' => $seeTestFlightUrl]);
    }

    public function fengDownload(Request $request){
        $fengTestFlightUrl = SystemSetting::getValueByName('fengTestFlightUrl') ? : '';
        return view('feng-download', ['testFlightUrl' => $fengTestFlightUrl]);
    }

}
