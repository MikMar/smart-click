<?php

use App\Job;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue_name')->index();
            $table->enum('priority', [
                Job::PRIORITY_NORMAL,
                Job::PRIORITY_HIGH
            ]);
            $table->enum('status', [
                Job::STATUS_PENDING,
                Job::STATUS_FINISHED,
                Job::STATUS_FAILED
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
