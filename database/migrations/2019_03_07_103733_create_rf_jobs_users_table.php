<?php

use App\JobUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRfJobsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rf_jobs_users', function (Blueprint $table) {
            $table->integer('job_id');
            $table->integer('user_id');
            $table->enum('status', [
                JobUser::STATUS_PENDING,
                JobUser::STATUS_SENT,
                JobUser::STATUS_FAILED
            ]);
            $table->unique(['job_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rf_jobs_users');
    }
}
