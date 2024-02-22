<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariableCharge extends Model
{
    use HasFactory;
    public $fillable = [
        'designation',
        'create_id',
        'status',
        'has_detail'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
