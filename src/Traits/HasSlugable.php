<?php

namespace Rayiumir\Slugable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait HasSlugable
{
    /**
     * Boot the trait HasSlugable.
     */
    public static function bootHasSlugable(): void
    {
        static::saving(function (Model $model) {
            $model->generateSlug();
        });
    }

    /**
     * Generate slug for the model
     */
    public function generateSlug(): void
    {
        $source = $this->slugSourceField ?? 'title';
        $destination = $this->slugDestinationField ?? 'slug';
        $separator = $this->slugSeparator ?? '-';
        $language = $this->slugLanguage ?? 'fa'; // fa, en, ar supported
        $maxLength = $this->slugMaxLength ?? 250;
        $forceUpdate = $this->slugForceUpdate ?? false;

        if (($forceUpdate || empty($this->$destination)) && !empty($this->$source)) {
            $slug = $this->convertToSlug($this->$source, $separator, $language);
            
            // Ensure slug is unique if needed
            if ($this->slugShouldBeUnique ?? true) {
                $slug = $this->makeSlugUnique($slug, $destination, $separator);
            }
            
            // Limit slug length
            $this->$destination = Str::limit($slug, $maxLength, '');
        }
    }

    /**
     * Convert string to slug
     */
    protected function convertToSlug(
        string $value, 
        string $separator = '-', 
        string $language = 'fa'
    ): string {
        // Convert numbers first
        $value = $this->convertNumbers($value);
        
        // Language specific processing
        $value = $this->processLanguageSpecificChars($value, $language);
        
        // Remove invisible characters
        $value = preg_replace('/[\x{200C}\x{200D}]/u', '', $value);
        
        // Replace spaces and underscores
        $value = str_replace([' ', '_'], $separator, $value);
        
        // Remove special characters
        $pattern = $this->getCharacterPatternForLanguage($language);
        $value = preg_replace("/[^{$pattern}a-zA-Z0-9{$separator}]/u", '', $value);
        
        // Clean up separators
        return $this->cleanUpSeparators($value, $separator);
    }

    /**
     * Process language specific characters
     */
    protected function processLanguageSpecificChars(string $value, string $language): string
    {
        switch ($language) {
            case 'fa':
                // Persian specific processing
                $value = str_replace(['‌', 'ـ'], '', $value); // Remove ZWNJ and Tatweel
                break;
            case 'ar':
                // Arabic specific processing
                $value = str_replace('ـ', '', $value); // Remove Tatweel
                break;
        }
        
        return $value;
    }

    /**
     * Get character pattern based on language
     */
    protected function getCharacterPatternForLanguage(string $language): string
    {
        $patterns = [
            'fa' => '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFD}\x{FE70}-\x{FEFF}',
            'ar' => '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFD}\x{FE70}-\x{FEFF}',
            'en' => '',
        ];
        
        return $patterns[$language] ?? $patterns['fa'];
    }

    /**
     * Clean up separators in slug
     */
    protected function cleanUpSeparators(string $value, string $separator): string
    {
        $value = preg_replace("/{$separator}+/", $separator, $value);
        return trim($value, $separator);
    }

    /**
     * Convert Persian and Arabic numbers to English
     */
    protected function convertNumbers(string $string): string
    {
        $numbers = [
            // Persian
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            // Arabic
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            // Hindi (optional)
            '०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4',
            '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9',
        ];
        
        return strtr($string, $numbers);
    }

    /**
     * Make slug unique by appending counter if needed
     */
    protected function makeSlugUnique(string $slug, string $destination, string $separator): string
    {
        $originalSlug = $slug;
        $counter = 2;
        
        while ($this->slugExists($slug, $destination)) {
            $slug = $originalSlug . $separator . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check if slug exists in database
     */
    protected function slugExists(string $slug, string $destination): bool
    {
        $query = static::where($destination, $slug);
        
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }
        
        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }
        
        return $query->exists();
    }

    /**
     * Check if model uses soft deletes
     */
    protected function usesSoftDeletes(): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this));
    }

    /**
     * Get the route key name for the model (optional)
     */
    public function getRouteKeyName(): string
    {
        return $this->slugDestinationField ?? 'slug';
    }
}
