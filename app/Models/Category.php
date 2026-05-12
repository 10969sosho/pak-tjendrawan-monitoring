<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (blank($category->slug) || $category->isDirty('name')) {
                $baseSlug = Str::slug($category->name);
                $slug = $baseSlug;

                $counter = 2;
                while (
                    static::query()
                        ->where('slug', $slug)
                        ->when($category->exists, fn ($query) => $query->whereKeyNot($category->getKey()))
                        ->exists()
                ) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }

                $category->slug = $slug;
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
