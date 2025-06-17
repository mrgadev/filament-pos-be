<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'name',
        'image',
        'is_cash'
    ];

    protected $casts = [
        'is_cash' => 'boolean'
    ];

    public function orders() {
        return $this->hasMany(Order::class);
    }
}
