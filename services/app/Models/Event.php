<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'budget_id',
        'title',
        'start_date',
        'end_date',
        'create_id'
    ];

    public function budget()
    {
        return $this->belongsTo('App\Model\Budget');
    }
}
