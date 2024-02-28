<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeChallan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // protected static function boot()
    // {
    //     parent::boot();
    //     self::creating(function($model) {
    //         // $challan_no=10000;
    //         $challan = FeeChallan::withTrashed()->latest()->first();
    //         if($challan){
    //             $challan_no = $challan->challan_no;
    //         }else {
    //             $challan_no = 1;
    //         }
    //          $challan_no++;
    //          if (!$model->challan_no >0) {
    //              # code...
    //              $model->challan_no = $challan_no ;
    //          }
    //     });
    // }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeChallanDetails()
    {
        return $this->hasMany(FeeChallanDetail::class);
    }

    public function parent()
    {
        return $this->belongsTo(FeeChallan::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(FeeChallan::class, 'parent_id');
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    
     public function feeChallanDetails1()
    {
        return $this->hasOne(FeeChallanDetail::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function scopeTopChallan($query)
    {
        return $query->where('parent_id', null);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
