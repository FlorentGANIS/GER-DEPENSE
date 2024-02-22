<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementUnit extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    
    protected $fillable = [
        'label'
    ];
}
