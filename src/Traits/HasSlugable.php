<?php

namespace Rayiumir\Slugable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlugable
{
    /**
     * Boot the trait Slugable.
     *
     * @return void
     */
    public static function bootHasSlugable(): void
    {
        static::saving(function (Model $model) {
            $source = $model->slugSourceField ?? 'title';
            $destination = $model->slugDestinationField ?? 'slug';

            if (empty($model->$destination) && !empty($model->$source)) {
                $model->$destination = static::makeMultilingualSlug($model->$source);
            }
        });
    }

    /**
     * Supported From Persian and Arabic to translate slug
     *
     * @param string $string
     * @return string
     */
    protected static function makeMultilingualSlug(string $string): string
    {
        $transliterationMap = [

            // Persian and Arabic

            'آ' => 'a', 'ا' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's', 'ج' => 'j',
            'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z', 'ر' => 'r', 'ز' => 'z',
            'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'z', 'ط' => 't', 'ظ' => 'z',
            'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'gh', 'ک' => 'k', 'گ' => 'g', 'ل' => 'l',
            'م' => 'm', 'ن' => 'n', 'و' => 'v', 'ه' => 'h', 'ی' => 'y', 'ء' => '', 'ئ' => 'y',
            'ؤ' => 'v', 'ة' => 'h', 'ي' => 'y',
        ];

        $string = preg_replace('/[\p{Mn}\p{Pd}\p{Zs}]/u', ' ', $string);

        $slug = str_replace(array_keys($transliterationMap), array_values($transliterationMap), $string);

        if (function_exists('transliterator_transliterate')) {
            $slug = transliterator_transliterate('Any-Latin; Latin-ASCII', $slug);
        }

        return Str::slug($slug);
    }
}
