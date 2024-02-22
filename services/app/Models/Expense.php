<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $keyType = 'string'; 
    
    protected $fillable = [
        'id',
        'repartition_id',
        'exp_amount',
        'management_unit_id',
        'quantity',
        'unit_price',
        'expense_date',
        'comment',
        'invoice_path',
        'create_id'
    ];

    public function repartition(){
        return $this->belongsTo('App\Models\Repartition');
    }

    public function management_unit(){
        return $this->belongsTo('App\Models\ManagementUnit', 'management_unit_id');
    }
}
