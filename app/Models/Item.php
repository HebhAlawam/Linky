<?php

namespace App\Models;

use App\Models\Concerns\HasArabicFallbackTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Item extends Model
{
    use HasArabicFallbackTranslations;
    use HasTranslations;

    public $translatable = [
        'title',
        'short_description',
        'description',
    ];

    protected $fillable = [
        'page_id',
        'category_id',
        'title',
        'slug',
        'short_description',
        'description',
        'action_type',
        'external_url',
        'price',
        'image',
        'is_featured',
        'is_visible',
        'display_order',
        'clicks',
    ];

    protected $casts = [
        'title' => 'array',
        'is_featured' => 'boolean',
        'is_visible' => 'boolean',
        'price' => 'array',

    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getTitleTextAttribute()
    {
        return $this->localizedText('title');
    }

    public function getShortDescriptionTextAttribute()
    {
        return $this->localizedText('short_description');
    }

    public function getDescriptionTextAttribute()
    {
        return $this->localizedText('description');
    }

    public function getPriceTextAttribute(): string
    {
        return $this->localizedPriceText('ar') ?: '—';
    }

    public function localizedPriceText(string $locale = 'ar'): string
    {
        return $this->localizedPriceDetails($locale)['base'];
    }

    public function localizedDiscountPriceText(string $locale = 'ar'): string
    {
        return $this->localizedPriceDetails($locale)['discount'];
    }

    public function localizedPriceDetails(string $locale = 'ar'): array
    {
        $price = $this->price;

        if (is_string($price)) {
            return ['base' => trim($price), 'discount' => ''];
        }

        if (! is_array($price)) {
            return ['base' => '', 'discount' => ''];
        }

        $selectedLocale = collect([$locale, $locale === 'ar' ? 'en' : 'ar'])
            ->first(fn ($candidate) => is_scalar($price[$candidate] ?? null) && filled($price[$candidate]));
        $base = $selectedLocale ? trim((string) $price[$selectedLocale]) : '';
        $discountValue = $selectedLocale && is_array($price['discount_price'] ?? null)
            ? $price['discount_price'][$selectedLocale] ?? null
            : null;
        $discount = is_scalar($discountValue) ? trim((string) $discountValue) : '';

        if ($base === '' || $discount === '') {
            $discount = '';
        }

        return ['base' => $base, 'discount' => $discount];
    }

    public function getUrlAttribute()
    {
        if ($this->action_type === 'external' && filled($this->external_url)) {
            return $this->external_url;
        }

        $page = $this->relationLoaded('page') ? $this->page : null;
        $pageSlug = $page?->slug;

        if (! $pageSlug && $this->page_id) {
            $pageSlug = Page::query()->whereKey($this->page_id)->value('slug');
        }

        return $pageSlug
            ? url("/{$pageSlug}/item/{$this->slug}")
            : '#';
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/defaults/item.svg');
    }

    public function getCategoryNameTextAttribute()
    {
        return $this->category?->name_text ?: '—';
    }

    public function publicTranslations(): array
    {
        return $this->translationPayload();
    }

    public function incrementClicks()
    {
        $this->increment('clicks');

        PageDailyStat::forToday($this->page_id)->increment('item_clicks');
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            if ($item->slug) {
                return;
            }

            $slug = Str::slug($item->slugSourceFromTranslations('title')) ?: 'item';
            $item->slug = $slug . '-' . uniqid();
        });
    }
}
