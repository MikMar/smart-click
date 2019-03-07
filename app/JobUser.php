<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobUser extends Model
{
    protected $table = 'rf_jobs_users';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
