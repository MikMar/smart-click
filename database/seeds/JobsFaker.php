<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Job;
use App\JobUser;

class JobsFaker extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create('en_ZA');

        DB::table('jobs')->truncate();
        $data = [];

        for ($i = 1; $i <= 10; $i++) {
            $data[$i]['queue_name'] = 'email';
            $data[$i]['priority'] = $faker->randomElement([Job::PRIORITY_NORMAL, Job::PRIORITY_HIGH]);
            $data[$i]['status'] = $faker->randomElement([Job::STATUS_PENDING, Job::STATUS_FINISHED, Job::STATUS_FAILED]);
        }

        DB::table('jobs')->insert($data);

        /////////////////////////////////////////////
        DB::table('rf_jobs_users')->truncate();

        for ($i = 1; $i <= 10000; $i++) {
            $data = [];
            $data[$i]['job_id'] = $faker->numberBetween(1, 10);
            $data[$i]['user_id'] = $faker->numberBetween(1, 32680);
            $data[$i]['status'] = $faker->randomElement([JobUser::STATUS_PENDING, JobUser::STATUS_SENT, JobUser::STATUS_FAILED]);

            if (!JobUser::where('job_id', $data[$i]['job_id'])->where('user_id', $data[$i]['job_id'])->exists()) {

                echo $i . ' ';

                DB::table('rf_jobs_users')->insert($data);

            }
        }
    }
}
