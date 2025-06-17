<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'gender',
        'phone',
        'birthday',
        'total_price',
        'note',
        'payment_method_id',
        'paid_amount',
        'change_amount'
    ];

    public function payment_method() {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function order_products() {
        return $this->hasMany(OrderProduct::class);
    }
}
