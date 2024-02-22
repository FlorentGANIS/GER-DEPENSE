<?php

namespace App\Models;

use App\Http\Controllers\SavingController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repartition extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    
    protected $fillable = [
        'id',
        'budget_id',
        'category_id',
        'rep_amount',
        'create_id'
    ];

    public function budget(){
        return $this->belongsTo(Budget::class);
    }

    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id');
    }
}
