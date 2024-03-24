<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepartitionIncomeBudget extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string';
    protected $fillable = [
        'id',
        'repartition_id',
        'income_budget_id',
        'amount_rep_inc_budget',
        'create_id',
    ];

    public function repartition(){
        return $this->belongsTo(Repartition::class);
    }

    public function income_budget(){
        return $this->belongsTo(IncomeBudget::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
