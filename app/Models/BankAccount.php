<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function bank_account_category()
    {
        return $this->belongsTo(BankAccountCategory::class);
    }

    public function sub_account()
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function fee_challans()
    {
        return $this->hasMany(FeeChallan::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function Employee_salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }
}
