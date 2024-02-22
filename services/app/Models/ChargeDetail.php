<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeDetail extends Model
{
    use HasFactory;
    public $fillable = [
        'designation',
        'quantity',
        'unit_price',
        'cd_amount',
        'variable_charge_id',
        'budget_id'
    ];

    public function variableCharge(){
        $this->belongsTo(VariableCharge::class);
    }

    public function budget(){
        $this->belongsTo(Budget::class);
    }
}
