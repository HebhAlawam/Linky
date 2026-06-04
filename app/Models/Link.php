<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'page_id',
        'title',
        'url',
        'icon',
        'type',
        'display_order',
        'clicks',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'display_order' => 'integer',
    ];

    public function getTitleTextAttribute(): string
    {
        $payload = $this->title_payload;

        return $payload['ar']
            ?? $payload['en']
            ?? collect($payload)->first(fn ($value) => filled($value))
            ?? '';
    }

    public function getTitlePayloadAttribute(): array
    {
        $title = $this->attributes['title'] ?? '';

        if (is_array($title)) {
            return $this->normalizeTitlePayload($title);
        }

        if (is_string($title)) {
            $decoded = json_decode($title, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeTitlePayload($decoded);
            }

            return ['ar' => $title, 'en' => null];
        }

        return ['ar' => null, 'en' => null];
    }

    private function normalizeTitlePayload(array $payload): array
    {
        return [
            'ar' => filled($payload['ar'] ?? null) ? (string) $payload['ar'] : null,
            'en' => filled($payload['en'] ?? null) ? (string) $payload['en'] : null,
        ];
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeSocial($query)
    {
        return $query->where('type', 'social');
    }

    public function incrementClicks()
    {
        $this->increment('clicks');

        PageDailyStat::forToday($this->page_id)->increment('link_clicks');
    }
}
