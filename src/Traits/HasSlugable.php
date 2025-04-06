<?php

namespace Rayiumir\Slugable\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlugable
{
    /**
     * Boot the trait HasSlugable.
     **/
    public static function bootHasSlugable(): void
    {
        static::saving(function (Model $model) {
            $source = $model->slugSourceField ?? 'title';
            $destination = $model->slugDestinationField ?? 'slug';

            if (empty($model->$destination) && !empty($model->$source)) {
                $slug = static::generateSlugFrom($model->$source);
                $model->$destination = static::makeSlugUnique($model, $slug, $destination);
            }
        });
    }

    /**
     * Supported From Persian and Arabic to translate slug
     **/

    protected static function generateSlugFrom(string $value): string
    {
        $value = static::convertToAscii($value);
        return Str::slug($value);
    }

    protected static function convertToAscii(string $string): string
    {
        $map = [
            'ا' => 'a', 'أ' => 'a', 'آ' => 'a', 'إ' => 'e', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'چ' => 'ch',
            'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ش' => 'sh',
            'ص' => 's', 'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'gh', 'ک' => 'k',
            'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'و' => 'v', 'ه' => 'h', 'ی' => 'y', 'ي' => 'y', 'ئ' => 'y', 'ة' => 'h',
            '‌' => '-',

            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
        ];

        return strtr($string, $map);
    }

    protected static function makeSlugUnique(Model $model, string $slug, string $column): string
    {
        $original = $slug;
        $i = 1;

        while ($model->newQuery()->where($column, $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }

}
