<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendemailController extends Controller
{
    public static function send($to, $subject, $message)
    {
        Mail::send(
            'emails.content',
            ['content' => $message],
            function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            }
        );
        return TRUE;
    }

    /**
     * 发送自定义网页
     * @param $viewPage
     * @param $emailData
     * @param array $viewData
     * @return bool
     */
    public static function sendHtml($viewPage, $emailData, $viewData = [])
    {
        Mail::send($viewPage, $viewData,
            function ($message) use ($emailData) {
                $message->to($emailData['email'])->subject($emailData['subject']);
            }
        );
        return TRUE;
    }


}
