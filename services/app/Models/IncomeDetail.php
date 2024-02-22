<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'income_id',
        'inc_det_amount',
        'label',
        'budget_id'
    ];

    public function income(){
        return $this->belongsTo('App\Models\Income', 'income_id');
    }

    public function budget(){
        return $this->belongsTo('App\Models\Budget', 'budget_id');
    }
}
