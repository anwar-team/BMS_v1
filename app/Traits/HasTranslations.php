<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTranslations
{
    /**
     * Get translatable attributes
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Get translation for given locale
     */
    public function getTranslation(string $attribute, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        
        $translations = $this->getTranslations($attribute);
        
        return $translations[$locale] ?? $translations[config('app.fallback_locale')] ?? '';
    }

    /**
     * Get all translations for given attribute
     */
    public function getTranslations(string $attribute): array
    {
        return json_decode($this->getAttributes()[$attribute] ?? '{}', true) ?: [];
    }

    /**
     * Set translation for given locale
     */
    public function setTranslation(string $attribute, string $locale, string $value): self
    {
        $translations = $this->getTranslations($attribute);
        $translations[$locale] = $value;
        
        $this->attributes[$attribute] = json_encode($translations);
        
        return $this;
    }

    /**
     * Set multiple translations
     */
    public function setTranslations(string $attribute, array $translations): self
    {
        $this->attributes[$attribute] = json_encode($translations);
        
        return $this;
    }

    /**
     * Scope to filter by translation
     */
    public function scopeWhereTranslation(Builder $query, string $attribute, string $value, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();
        
        return $query->where($attribute, 'like', "%\"$locale\":\"%$value%\"%");
    }

    /**
     * Get attribute with automatic translation
     */
    public function getTranslatedAttribute(string $attribute): string
    {
        if (in_array($attribute, $this->getTranslatableAttributes())) {
            return $this->getTranslation($attribute);
        }
        
        return $this->getAttribute($attribute);
    }

    /**
     * Cast translatable attributes to array
     */
    protected function initializeHasTranslations()
    {
        foreach ($this->getTranslatableAttributes() as $attribute) {
            $this->casts[$attribute] = 'array';
        }
    }
}