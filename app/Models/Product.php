<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'stock',
        'price',
        'is_active',
        'image',
        'description',
        'barcode',
    ];

    protected $appends = [
        'image_url',
        'price_formatted',
    ];


    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function order_products() {
        return $this->hasMany(OrderProduct::class);
    }

    public static function generateUniqueSlug(String $name) {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        while(self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getImageUrlAttribute() {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getPriceFormattedAttribute() {
        return 'Rp. ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeSearch($query, $value) {
        $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('slug', 'like', '%' . $value . '%')
            ->orWhere('barcode', 'like', '%' . $value . '%');
    }
}
