<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function jobStatus()
    {
        return $this->belongsTo(JobStatus::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function payScale()
    {
        return $this->belongsTo(PayScale::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function salaryAllowance()
    {
        return $this->hasOne(SalaryAllowance::class);
    }

    public function salaryDeduction()
    {
        return $this->hasOne(SalaryDeduction::class);
    }

    public function GPFund()
    {
        return $this->hasOne(GPFund::class);
    }

    public function employeeSalaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }
public function employeeSalaries1()
    {
        return $this->hasOne(EmployeeSalary::class)->orderBy('created_at', 'desc');
    }
    public function emp_lectures()
    {
        return $this->hasMany(EmpLecture::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
