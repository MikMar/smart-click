<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new JobScope);
    }

    protected $table = 'jobs';

    const STATUS_PENDING = 'pending';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    const PRIORITY_HIGH = 'high';
    const PRIORITY_NORMAL = 'normal';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function jobUsers()
    {
        return $this->hasMany(JobUser::class, 'job_id', 'id')->active();
    }

    public function scopeEmailQueue($query)
    {
        return $query->where('queue_name', 'email');
    }
}
