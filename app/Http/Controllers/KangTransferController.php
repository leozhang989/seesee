<?php

namespace App\Http\Controllers;


use App\Models\Appuser;
use App\Models\Device;
use App\Models\DeviceTypeMaps;
use App\Models\DeviceIdentifierMaps;
use App\Models\FlowerTransferLogs;
use App\Models\Seedevice;
use App\Models\Seeuser;
use App\Models\SystemSetting;
use App\Models\TransferLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\See;

class KangTransferController extends Controller
{
    public function kangTransferPage(){
        return view('kang-transfer');
    }

    public function kangTransferApi(string $account, string $payTime, string $pwd, string $code = ''){
        DB::beginTransaction();
        try {
            if(empty($account) || empty($payTime) || empty($pwd))
                throw new \Exception('参数不完整');

            if($pwd !== '889900')
                throw new \Exception('admin密码错误');

            if(!strtotime($payTime))
                throw new \Exception('时间格式错误');

            $now = time();
            if($code) {
                $devicelog = FlowerTransferLogs::where('kang_device_code', $code)->first();
                if ($devicelog)
                    throw new \Exception('这台康设备已经转移过了');
            }

            $user = Seeuser::where('email', $account)->orWhere('uuid', $account)->first();
            if(empty($user))
                throw new \Exception('账号或UUID错误，会员不存在');

            if($user['is_permanent_vip'] === 1)
                throw new \Exception('用户已开通过永久会员');

            //判断设备型号对不对
            $devices = Seedevice::where('uid', $user['id'])->get()->toArray();
            if(empty($devices))
                throw new \Exception('未查询到用户设备，稍后重试');

            $deviceId = $this->checkDeviceModel($payTime, $devices);
            $device = Seedevice::find($deviceId);

            $logData = [
                'device_code' => $device['device_code'] ?? '',
                'email' => $user['email'] ?? '',
                'transfer_time' => $now,
                'vip_type' => 'permanent-vip',
                'is_kang' => 1,
                'kang_pay_time' => date('Y-m-d H:i:s', strtotime($payTime)),
                'kang_device_code' => $code ?? ''
            ];
            $newlog = FlowerTransferLogs::create($logData);
            if($newlog){
                $start = $user->vip_expired > $now ? $user->vip_expired : $now;
                $user->vip_expired = strtotime('+10 years', $start);
                $user->is_permanent_vip = 1;
                $user->save();
            }
            DB::commit();
            return response()->json(['msg' => '开通成功，到期时间是：' . date('Y-m-d H:i:s', $user['vip_expired']), 'data' => '', 'code' => 200]);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage(), 'data' => '', 'code' => 202]);
        }
    }

    protected function checkDeviceModel(string $payTime, array $devices){
        $userPayTime = date('Y-m-d', strtotime($payTime));
        $correct = 0;
        $newestTime = '0000-00-00';
        $empty = 0;
        $typeString = '';
        foreach ($devices as $device) {
            if(empty($device['device_identifier'])){
                $empty = 1;
                continue;
            }
            $empty = 0;

            //user device type
            $userDeviceType = DeviceIdentifierMaps::where('device_identifier', $device['device_identifier'])->first();
            $typeString .= $userDeviceType['device_type'] ? $userDeviceType['device_type'] . ' ' : '';

            $mapDevice = DeviceIdentifierMaps::where('device_identifier', $device['device_identifier'])->where('starttime', '<=', $userPayTime)->first();
            if($mapDevice && (strtotime($mapDevice['starttime']) > strtotime($newestTime))) {
                $newestTime = $mapDevice['starttime'];
                $correct = $device['id'];
            }
        }
        if($empty)
            throw new \Exception('用户设备类型未获取到，需要人工确认设备类型');

        if(empty($correct)){
            $ext = $typeString ? '用户当前的机型有：' . $typeString : '';
            throw new \Exception('用户没有满足要求的设备型号' . $ext);
        }

        return $correct;
    }

    public function webpermanentTransferPage(string $uuid)
    {
        $user = Seeuser::where('uuid', $uuid)->first();
        if(empty($user))
            return '';

        $now = Carbon::now();
        $vipExpired = Carbon::createFromTimestamp($user['vip_expired'])->toDateString();
        $days = Carbon::createFromTimestamp($user['vip_expired'])->diffInDays($now);
        return view('webpermanent-zhuanyi', ['email' => $user['email'], 'expired' => $vipExpired, 'days' => $days]);
    }

    public function webOrdTransferPage(string $uuid)
    {
        $user = Seeuser::where('uuid', $uuid)->first();
        if(empty($user))
            return '';

        return view('webord-zhuanyi', ['email' => $user['email']]);
    }
}
