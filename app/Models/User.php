<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * | store
     */
    public function store(array $req)
    {
        User::create($req);
    }

    /**
     * | Get User by Email
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)
            ->where('is_deleted', 0)
            ->get();
    }

    /**
     * | Get User By School Id
     */
    public function getUserBySchoolId($schoolId)
    {
        return User::where('school_id', $schoolId)
            ->where('is_deleted', 0)
            ->firstOrFail();
    }
}
