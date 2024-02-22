<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Budget extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    protected $fillable = [
        'id',
        'month_id',
        'year_budget',
        'status',
        'global_amount',
        'remaining_amount',
        'total_incomes',
        'total_expenses',
        'balance',
        'create_id'
    ];

    public function incomes()
    {
        return $this->belongsToMany(Income::class, 'income_budgets');
    }

    public function month()
    {
        return $this->belongsTo(Month::class, 'month_id');
    }

    

    
}
