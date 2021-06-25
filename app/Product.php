<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Product extends Model
{
    use SoftDeletes;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'price',
        'cogs',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
        'img',
        'bv',
        'discount',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'cogs_products', 'products_id', 'accounts_id')
        ->withPivot([
            'amount'
        ]);
    }
    
    public function orderdetailsSum()
    {
        return $this->hasMany(OrderDetails::class, 'products_id')
        ->select('quantity')
        ->groupBy('products_id');
    }
}
