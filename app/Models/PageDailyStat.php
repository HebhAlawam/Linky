<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PageDailyStat extends Model
{
    protected $fillable = [
        'page_id',
        'date',
        'visits',
        'unique_visits',
        'item_views',
        'item_clicks',
        'link_clicks',
        'whatsapp_order_attempts',
        'copied_order_attempts',
    ];

    protected $casts = [
        'date' => 'date',
        'visits' => 'integer',
        'unique_visits' => 'integer',
        'item_views' => 'integer',
        'item_clicks' => 'integer',
        'link_clicks' => 'integer',
        'whatsapp_order_attempts' => 'integer',
        'copied_order_attempts' => 'integer',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeForDate($query, Carbon|string $date)
    {
        return $query->whereDate('date', $date);
    }

    public static function forToday(int $pageId): self
    {
        return self::query()
            ->where('page_id', $pageId)
            ->whereDate('date', now()->toDateString())
            ->first()
            ?? self::query()->create([
                'page_id' => $pageId,
                'date' => now()->toDateString(),
            ]);
    }

    public function recordVisit(bool $unique = false): void
    {
        $this->increment('visits');

        if ($unique) {
            $this->increment('unique_visits');
        }
    }

    public function recordOrderAttempt(string $channel): void
    {
        match ($channel) {
            'whatsapp' => $this->increment('whatsapp_order_attempts'),
            'copy' => $this->increment('copied_order_attempts'),
        };
    }
}
