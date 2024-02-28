<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_members';

    protected $fillable = [
        'campus_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'applied_for',
        'full_name',
        'father_name',
        'nationality',
        'religion',
        'experience',
        'cnic_no',
        'qualification',
        'gender',
        'marital_status',
        'dob',
        'remarks',
        'mobile_no',
        'phone',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
