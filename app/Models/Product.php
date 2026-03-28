<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company',
        'model',
        'size',
        'color',
        'grade',
        'purchase_price',
        'average_cost',
        'sale_price',
        'quantity_meter',
        'full_name',
    ];
}