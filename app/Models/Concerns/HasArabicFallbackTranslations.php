<?php

namespace App\Models\Concerns;

/**
 * Shared translation helper for models that use Spatie HasTranslations.
 *
 * Purpose:
 * - Keep the dashboard Arabic-first by returning Arabic text first,
 *   then English, then any available translation as a fallback.
 * - Provide full translation payloads for the public frontend,
 *   so the frontend can switch languages without depending on Laravel locale.
 * - Provide a clean source value for slug generation,
 *   preferring English first because it produces cleaner URLs.
 *
 * Used by models such as Page, Category, and Item.
 */

trait HasArabicFallbackTranslations
{
    public function localizedText(string $field): string
    {
        $translations = $this->translationPayload($field);

        return (string) ($translations['ar'] ?? $translations['en'] ?? reset($translations) ?: '');
    }

    public function translationPayload(?string $field = null): array
    {
        if ($field !== null) {
            if (method_exists($this, 'getTranslations') && in_array($field, $this->translatable ?? [], true)) {
                return $this->getTranslations($field);
            }

            $value = $this->getAttribute($field);

            if (is_array($value)) {
                return $value;
            }

            return filled($value) ? ['ar' => $value] : [];
        }

        $payload = [];

        foreach ($this->translatable ?? [] as $translatableField) {
            $payload[$translatableField] = $this->translationPayload($translatableField);
        }

        return $payload;
    }

    protected function slugSourceFromTranslations(string $field): string
    {
        $translations = $this->translationPayload($field);

        return (string) ($translations['en'] ?? $translations['ar'] ?? reset($translations) ?: '');
    }
}
