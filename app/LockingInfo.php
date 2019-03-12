<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LockingInfo extends Model
{
    protected $table = 'locking_info';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
