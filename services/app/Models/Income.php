<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    
    protected $fillable = [
        'id',
        'label',
        'create_id'
    ];

    public function budgets()
    {
        return $this->belongsToMany(Budget::class, 'income_budgets');
    }
}
