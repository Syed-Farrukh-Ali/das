<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $integers = 1;
            $old_voucher_no = Voucher::where('voucher_type_id', $model->voucher_type_id)->latest()->first();
            if ($old_voucher_no) {
                $integers = explode('-', $old_voucher_no->voucher_no, 2)[1];
            }

            $integers++;
            $model->voucher_no = VoucherType::find($model->voucher_type_id)->name.'-'.$integers;
        });
    }

    public function general_ledgers()
    {
        return $this->hasMany(GeneralLedger::class);
    }

    public function employee_salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function fee_challans()
    {
        return $this->hasMany(FeeChallan::class);
    }

    public function voucher_type()
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function fee_return()
    {
        return $this->hasMany(FeeReturn::class);
    }
}
