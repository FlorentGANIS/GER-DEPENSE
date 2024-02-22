<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    protected $fillable = [
        'id',
        'designation',
        'create_id',
        'status',
        'type',
        'fixed_amount'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
