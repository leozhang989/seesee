<?php

namespace App\Console\Commands;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDeviceTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clearDevice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动清理设备';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = Carbon::now();
        $res = DB::statement("CREATE TABLE `devices_bak_{$time}` SELECT * FROM `devices`");
        if($res) {
//            Device::where('uid', '!=', 0)->delete();
        }
    }
}
