<?php

namespace App\Models;

use App\Models\Auth\Traits\Method\UserMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, UserMethod;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'campus_id',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function campus()
    {
        return $this->hasOne(Campus::class);
    }

    public function staff_member()
    {
        return $this->hasOne(StaffMember::class);
    }

    public function head_office()
    {
        return $this->hasOne(HeadOffice::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
