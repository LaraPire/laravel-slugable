<?php

namespace Rayiumir\Slugable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait HasSlugable
{
    private const LANGUAGE_PATTERNS = [
        'fa' => '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFD}\x{FE70}-\x{FEFF}',
        'ar' => '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFD}\x{FE70}-\x{FEFF}',
        'en' => '',
    ];

    private const NUMBER_MAP = [
        // Persian
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        // Arabic
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        // Hindi
        '०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4',
        '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9',
    ];

    /**
     * Boot the trait HasSlugable.
     */
    public static function bootHasSlugable(): void
    {
        static::saving(fn (Model $model) => $model->generateSlug());
    }

    /**
     * Generate slug for the model.
     */
    public function generateSlug(): void
    {
        $config = $this->getSlugConfiguration();

        if ($this->shouldGenerateSlug($config)) {
            $slug = $this->createSlug($this->{$config['source']}, $config);
            
            $this->{$config['destination']} = Str::limit(
                $config['unique'] ? $this->ensureSlugUniqueness($slug, $config) : $slug,
                $config['maxLength'],
                ''
            );
        }
    }

    /**
     * Get slug configuration with defaults.
     */
    protected function getSlugConfiguration(): array
    {
        return [
            'source' => $this->slugSourceField ?? 'title',
            'destination' => $this->slugDestinationField ?? 'slug',
            'separator' => $this->slugSeparator ?? '-',
            'language' => $this->slugLanguage ?? 'fa',
            'maxLength' => $this->slugMaxLength ?? 250,
            'forceUpdate' => $this->slugForceUpdate ?? false,
            'unique' => $this->slugShouldBeUnique ?? true,
            'useForRoutes' => $this->slugUseForRoutes ?? false,
        ];
    }

    /**
     * Determine if we should generate a slug.
     */
    protected function shouldGenerateSlug(array $config): bool
    {
        return ($config['forceUpdate'] || empty($this->{$config['destination']})) 
            && !empty($this->{$config['source']});
    }

    /**
     * Create a slug from the given value.
     */
    protected function createSlug(string $value, array $config): string
    {
        return $this->cleanUpSeparators(
            $this->processSlugString($value, $config),
            $config['separator']
        );
    }

    /**
     * Process the string to create a slug.
     */
    protected function processSlugString(string $value, array $config): string
    {
        $value = $this->convertNumbers($value);
        $value = $this->processLanguageSpecificChars($value, $config['language']);
        
        // Remove invisible characters
        $value = preg_replace('/[\x{200C}\x{200D}]/u', '', $value);
        
        // Replace spaces and underscores
        $value = str_replace([' ', '_'], $config['separator'], $value);
        
        // Remove special characters
        $pattern = self::LANGUAGE_PATTERNS[$config['language']] ?? self::LANGUAGE_PATTERNS['fa'];
        $value = preg_replace("/[^{$pattern}a-zA-Z0-9{$config['separator']}]/u", '', $value);
        
        return $value;
    }

    /**
     * Process language specific characters.
     */
    protected function processLanguageSpecificChars(string $value, string $language): string
    {
        return match ($language) {
            'fa' => str_replace(['‌', 'ـ'], '', $value), // Remove ZWNJ and Tatweel
            'ar' => str_replace('ـ', '', $value),       // Remove Tatweel
            default => $value,
        };
    }

    /**
     * Clean up separators in slug.
     */
    protected function cleanUpSeparators(string $value, string $separator): string
    {
        $value = preg_replace("/{$separator}+/", $separator, $value);
        return trim($value, $separator);
    }

    /**
     * Convert numbers to English numerals.
     */
    protected function convertNumbers(string $string): string
    {
        return strtr($string, self::NUMBER_MAP);
    }

    /**
     * Ensure slug uniqueness by appending counter if needed.
     */
    protected function ensureSlugUniqueness(string $slug, array $config): string
    {
        $originalSlug = $slug;
        $counter = 2;
        
        while ($this->slugExists($slug, $config['destination'])) {
            $slug = "{$originalSlug}{$config['separator']}{$counter}";
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check if slug exists in database.
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
     * Check if model uses soft deletes.
     */
    protected function usesSoftDeletes(): bool
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this), true);
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName(): string
    {
        $config = $this->getSlugConfiguration();
        
        return $config['useForRoutes'] ? $config['destination'] : 'id';
    }

    /**
     * Retrieve the model for a bound value.
     * This method allows the use of both id and slug at the same time.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $config = $this->getSlugConfiguration();
        
        if ($config['useForRoutes'] && $field === null) {
            $field = $config['destination'];
        }
        
        return $this->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    /**
     * Generate slug from a given string (helper method).
     */
    public static function generateSlugFrom(string $source, array $config = []): string
    {
        $instance = new static();
        $defaultConfig = $instance->getSlugConfiguration();
        $mergedConfig = array_merge($defaultConfig, $config);
        
        return $instance->createSlug($source, $mergedConfig);
    }
}
