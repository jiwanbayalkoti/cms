<?php

namespace App\Models;

use App\Models\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillHistory extends Model
{
    use HasFactory, CompanyScoped;

    protected $table = 'bill_history';

    protected $fillable = [
        'bill_module_id',
        'company_id',
        'action',
        'user_id',
        'comment',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function billModule()
    {
        return $this->belongsTo(BillModule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
