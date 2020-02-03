<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeLog;
use Illuminate\Http\Request;

class NoticesController extends Controller
{
    public function noticeList(Request $request){
        return view('notice-list', []);
    }

    public function detail(Request $request, $nid = '', $uuid = ''){
        $noticeData = [];
        if($nid){
            $noticeData = Notice::find($nid);
            if($noticeData && $uuid) {
                $readLog = NoticeLog::where('notice_id', $noticeData['id'])->where('uuid', $uuid)->first();
                if(empty($readLog)){
                    NoticeLog::create([
                        'notice_id' => $noticeData['id'],
                        'uuid' => $uuid
                    ]);
                }
            }
        }
        return view('notice-detail', ['noticeContent' => $noticeData ? $noticeData['notice_content'] : '']);
    }
}
