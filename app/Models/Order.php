<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const PAYMENT_URI = "https://superpay.view.agentur-loop.com/pay";

    protected $fillable = ['customer_id', 'is_payed'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
//            ->withPivot('amount')
//            ->value('amount');
            ->as('amount')
            ->withPivot('amount');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
