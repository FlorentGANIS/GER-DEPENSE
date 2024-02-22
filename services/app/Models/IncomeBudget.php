<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeBudget extends Model
{
    use HasFactory;
    protected $fillable = [
        'income_id',
        'budget_id',
        'ib_amount',
        'create_id'
    ];

    public function income()
    {
        return $this->belongsTo('App\Models\Income');
    }

    public function budget()
    {
        return $this->belongsTo('App\Models\Budget');
    }
}
