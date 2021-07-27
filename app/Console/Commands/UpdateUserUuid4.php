<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\UserInfoController;

class UpdateUserUuid4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:uuid4';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新用户表uuid4';

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
    public function handle(UserInfoController $ch)
    {
        $ch->updateUserUuid4();
    }
}