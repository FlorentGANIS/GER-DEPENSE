<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryEnvelope extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'create_id',
        'category_id',
        'env_amount',
        'from_budget',
        'to_budget',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    public function to_budget()
    {
        return $this->belongsTo('App\Models\Budget', 'to_budget');
    }

    public function from_budget()
    {
        return $this->belongsTo('App\Models\Budget', 'from_budget');
    }
}
