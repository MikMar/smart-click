<?php

namespace App\Console\Commands;

use App\LockingInfo;
use DB;
use App\Job;
use App\JobUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

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

                $hash = bcrypt(strtotime(date('Y-m-d h:i:s')));
                LockingInfo::insert(['job_id' => $job->id, 'hash' => $hash]);

                Log::info(LockingInfo::where('job_id', $job->id)->get());

                $jobUsers = $job->jobUsers()->lockForUpdate()->take(5)->get();

                if (!count($jobUsers)) { // to share reading ability btw multiple workers

                    if (count(LockingInfo::where('job_id', $job->id)->get()) !== 1) {

                        Log::info('Showing no pending users, but locked rows are in process');

                    } else {

                        Log::info('There is no user in this job. Job gets finished status');
                        $job->status = Job::STATUS_FINISHED;
                        $job->save();

                    }

                }

                foreach ($jobUsers as $jobUser) {

                    $status = JobUser::STATUS_PENDING;

                    try {

                        $transport = new \Swift_SmtpTransport(env('MAIL_HOST', '127.0.0.1'), env('MAIL_PORT', 25));
                        $transport->setUsername(env('MAIL_USERNAME', ''));
                        $transport->setPassword(env('MAIL_PASSWORD', ''));
                        $transport->setEncryption(env('MAIL_ENCRYPTION', ''));
                        if (php_sapi_name() == 'cli') {
                            $transport->setLocalDomain(env('MAIL_LOCAL_DOMAIN', '127.0.0.1'));
                        }

                        $mailer = new \Swift_Mailer($transport);

                        $message = new \Swift_Message();
                        $message->setSubject('Mail from worker');
                        $message->setFrom(['sc@test.com' => 'No reply']);
                        $message->setTo($jobUser->user->email);

                        $bodyView = View::make('emails.test', []);
                        $message->setBody($bodyView->render(), 'text/html');

                        // adding plain text
                        $textView = View::make('emails.test' . '_text', []);
                        $message->addPart($textView->render(), 'text/plain');

                        $result = $mailer->send($message);

                        if ($result) {
                            $status = JobUser::STATUS_SENT;
                        }

                    } catch (\Exception $e) {

                        $status = JobUser::STATUS_FAILED;
                        Log::info($jobUser->user->email . ' ' . $e->getMessage() . ' ' . $e->getcode());
                    }

                    JobUser::where('job_id', $jobUser->job_id)
                        ->where('user_id', $jobUser->user_id)
                        ->update(['status' => $status]);
                }

                LockingInfo::where('job_id', $job->id)->where('hash', $hash)->delete();

            });
        }

    }
}
