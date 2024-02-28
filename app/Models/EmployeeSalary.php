<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalary extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
