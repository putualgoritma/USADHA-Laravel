<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Order extends Model
{
    public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'code',
        'memo',
        'register',
        'total',
        'type',
        'status',
        'ledgers_id',
        'customers_id',
        'payment_type',
        'agents_id',
        'bv_activation_amount',
        'customers_activation_id',
    ];

    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customers_id')->select('id', 'code', 'name');
    }

    public function agents()
    {
        return $this->belongsTo(Customer::class, 'agents_id');
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product', 'order_id', 'product_id')
        ->withPivot([
            'quantity',
            'price',
            'cogs',
            ])
        ->select(['products.id', 'products.price', 'products.name']);
    }

    public function productdetails()
    {
        return $this->belongsToMany(Product::class, 'product_order_details', 'orders_id', 'products_id')->withPivot([
            'quantity',
            'type',
            'status',
            'warehouses_id',
            'owner',
        ]);
    }

    public function points()
    {
        return $this->belongsToMany(Point::class, 'order_points', 'orders_id', 'points_id')->withPivot([
            'amount',
            'type',
            'status',
            'customers_id',
        ]);
    }

    public function scopeFilterInput($query)
    {
        if(!empty(request()->input('type'))){
            $type = request()->input('type'); 
            return $query->where('type', $type);
        }else{
            return $query->where(function ($querys) {
                $querys->where('type', 'sale')
                ->orWhere('type', 'topup')
                ->orWhere('type', 'sale_retur')
                ->orWhere('type', 'transfer')
                ->orWhere('type', 'withdraw')
                ->orWhere('type', 'production')
                ->orWhere('type', 'buy')
                ->orWhere('type', 'buy_retur')
                ->orWhere('type', 'agent_sale')
                ->orWhere('type', 'activation_member')
                ->orWhere('type', 'stock_trsf');
            });
        }
    }

    public function scopeFilterCustomer($query)
    {
        if(!empty(request()->input('customer'))){
            $customer = request()->input('customer'); 
            return $query->where('customers_id', $customer);
        }else{
            return ;
        }
    }

    public function accounts()
    {
        return $this->belongsTo(Account::class, 'acc_pay')->select('id', 'code', 'name');
    }
}
