<?php

namespace App\Console\Commands;

use DB;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Worker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running workers';

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
        $user = User::where('id', 1)->update(['name' => strtotime(date('Y-m-d h:i:s'))]);
        //$user->password = bcrypt('newpassword');

        /*DB::transaction(function () use ($user) {

            $user->save();

        });*/

        //Log::info($user);
        //Log::info('worker: users count ' . count($users));

    }
}
