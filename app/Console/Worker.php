<?php

namespace App\Console\Commands;

use DB;
use App\Job;
use App\JobUser;
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

        $jobs = Job::where('status', job::STATUS_PENDING)
            ->orderByDesc('id')
            ->get()
            ->groupBy('priority');

        if (!count($jobs)) {
            Log::info('There is no job');
        }

        $job = null;

        if (isset($jobs[Job::PRIORITY_HIGH])) {
            $job = $jobs[Job::PRIORITY_HIGH]->first();
        }
        if (!empty($job) && isset($jobs[Job::PRIORITY_NORMAL])) {
            $job = $jobs[Job::PRIORITY_NORMAL]->first();
        }

        if (!empty($job)) {

            DB::transaction(function () use ($job) {

                if (!count($job->jobUsers()->sharedLock()->get())) { // to share reading ability btw multiple workers

                    Log::info('There is no user in this job');
                    $job->status = Job::STATUS_FINISHED;
                    $job->save();
                    return;

                }

                foreach ($job->jobUsers as $jobUser) {
                    Log::info($jobUser->user_id);

                    JobUser::where('job_id', $jobUser->job_id)
                        ->where('user_id', $jobUser->user_id)
                        ->lockForUpdate()  // exclusive lock
                        ->update(['status' => JobUser::STATUS_SENT]);
                }

            });
        }

    }
}
