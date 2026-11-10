<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    public function isMainAdmin(): bool
    {
        $mainAdminId = config('admin.main_admin_user_id');

        return $this->role === 'admin'
            && is_string($mainAdminId)
            && $mainAdminId !== ''
            && (string) $this->getKey() === $mainAdminId;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'program',
        'year_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}