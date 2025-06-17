<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Category extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    public function products() {
        return $this->hasMany(Product::class);
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
}
