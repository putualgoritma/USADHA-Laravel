<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'price',
        'cogs',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
        'description',
        'img',
        'package_type',
        'bv',
        'activation_type_id',
        'discount',
        'upgrade_type_id',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'package_product', 'parent_id', 'products_id')->withPivot([
            'quantity'
        ]);
    }
}
