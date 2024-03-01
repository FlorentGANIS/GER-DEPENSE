<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    use HasFactory;
    protected $fillable = [
        'budget_id',
        'create_id',
        'repartition_id',
        'type',
        'prevision',
        'amount_used',
        'envelope_help',
        'category_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

}
