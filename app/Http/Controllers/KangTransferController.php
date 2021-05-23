<?php

namespace App\Http\Controllers;


use App\Models\Appuser;
use App\Models\Device;
use App\Models\DeviceTypeMaps;
use App\Models\DeviceIdentifierMaps;
use App\Models\FlowerTransferLogs;
use App\Models\SystemSetting;
use App\Models\TransferLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            if($pwd !== 'qwerty123')
                throw new \Exception('admin密码错误');

            if(!strtotime($payTime))
                throw new \Exception('时间格式错误');

            $now = time();
            if($code) {
                $devicelog = FlowerTransferLogs::where('kang_device_code', $code)->first();
                if ($devicelog)
                    throw new \Exception('这台康设备已经转移过了');
            }

            $user = Appuser::where('email', $account)->first();
            if(empty($user)){
                $device = Device::where('uuid', $account)->first();
                if(empty($device))
                    throw new \Exception('账号或UUID错误，会员不存在');
                if(empty($device['uid']))
                    throw new \Exception('该会员还未注册账号，无法开通');
                $user = Appuser::find($device['uid']);
                if(empty($user))
                    throw new \Exception('用户账号异常，未查询到账户，请稍后重试');
                $log = FlowerTransferLogs::where('email', $user['email'])->first();
                if($log)
                    throw new \Exception('用户已开通过永久会员');

                $this->checkDeviceModel($payTime, [$device]);
            }else{
                //判断设备型号对不对
                $devices = Device::where('uid', $user['id'])->get()->toArray();
                if(empty($devices))
                    throw new \Exception('未查询到用户设备，稍后重试');

                $deviceId = $this->checkDeviceModel($payTime, $devices);
                $device = Device::find($deviceId);
            }

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

}
