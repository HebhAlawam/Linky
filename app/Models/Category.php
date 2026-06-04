<?php

namespace App\Models;

use App\Models\Concerns\HasArabicFallbackTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasArabicFallbackTranslations;
    use HasTranslations;

    public $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'page_id',
        'name',
        'slug',
        'description',
        'image',
        'display_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class)->orderBy('display_order');
    }

    public function visibleItems()
    {
        return $this->hasMany(Item::class)
            ->visible()
            ->orderBy('display_order');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function getNameTextAttribute()
    {
        return $this->localizedText('name');
    }

    public function getDescriptionTextAttribute()
    {
        return $this->localizedText('description');
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/defaults/category.png');
    }

    public function publicTranslations(): array
    {
        return $this->translationPayload();
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if ($category->slug) {
                return;
            }

            $category->slug = Str::slug($category->slugSourceFromTranslations('name'))
                ?: 'category-' . uniqid();
        });
    }
}
