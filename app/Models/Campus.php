<?php

namespace App\Models;

use App\Models\Notification\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
   protected $visible = ['id','name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campusclass()
    {
        return $this->belongsTo(CampusClass::class);
    }

    public function staff_members()
    {
        return $this->hasMany(StaffMember::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function registrationCards()
    {
        return $this->hasMany(RegistrationCard::class);
    }

    public function feestructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function generalLedgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }

    public function tempgeneralLedgers()
    {
        return $this->hasMany(TempGeneralLedger::class);
    }

    public function employee_salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function printAccountNos()
    {
        return $this->hasMany(PrintAccountNo::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class);
    }

    public function support(): HasOne
    {
        return $this->hasOne(Support::class);
    }
}
