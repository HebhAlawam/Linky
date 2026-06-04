<?php

namespace App\Models;

use App\Models\Concerns\HasArabicFallbackTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasArabicFallbackTranslations;
    use HasTranslations;

    public const DEFAULT_SLOGAN = [
        'ar' => 'موقعك الاحترافي خلال دقائق',
        'en' => 'Your professional mini website in minutes',
    ];

    public $translatable = [
        'title',
        'slogan',
        'seo_title',
        'seo_description',
    ];

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'logo',
        'slogan',
        'type',
        'template',
        'domain',
        'seo_title',
        'seo_description',
        'settings',
        'status',
        'settings_version',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy('display_order');
    }

    public function visibleCategories()
    {
        return $this->hasMany(Category::class)
            ->visible()
            ->orderBy('display_order');
    }

    public function links()
    {
        return $this->hasMany(Link::class)->orderBy('display_order');
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

    public function featuredItems()
    {
        return $this->hasMany(Item::class)
            ->visible()
            ->featured()
            ->orderBy('display_order');
    }

    public function dailyStats()
    {
        return $this->hasMany(PageDailyStat::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getTitleTextAttribute()
    {
        return $this->localizedText('title');
    }

    public function getSloganTextAttribute()
    {
        return $this->localizedText('slogan') ?: self::DEFAULT_SLOGAN['ar'];
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/defaults/logo.svg');
    }

    public function getUrlAttribute()
    {
        return $this->domain
            ? 'https://' . $this->domain
            : url('/' . $this->slug);
    }

    public function publicTranslations(): array
    {
        return $this->translationPayload();
    }

    public function isBio()
    {
        return $this->type === 'bio';
    }

    public function isWebsite()
    {
        return $this->type === 'website';
    }

    protected static function booted()
    {
        static::creating(function ($page) {
            if ($page->slug) {
                return;
            }

            $slug = Str::slug($page->slugSourceFromTranslations('title')) ?: 'linky-page';
            $page->slug = $slug . '-' . uniqid();
        });
    }
}
