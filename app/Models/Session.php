<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function fee_structures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function fee_challans()
    {
        return $this->hasMany(FeeChallan::class);
    }

    public function employee_salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }
}
