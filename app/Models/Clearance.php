<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Clearance extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'clearances';

    protected $fillable = [
        'student_id',
        'department',
        'status',
        'remarks',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];
}
