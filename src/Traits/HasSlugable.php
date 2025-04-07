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
                $slug = self::convertToSlug($model->$source);
                $model->$destination = $slug;
            }
        });
    }

    protected static function convertToSlug(string $value): string
    {
        $value = preg_replace('/[\x{200C}\x{200D}]/u', '', $value);
        $value = str_replace([' ', '_'], '-', $value);
        $value = self::convertNumbers($value);
        $value = preg_replace('/[^\x{0600}-\x{06FF}\x{0750}-\x{077F}a-zA-Z0-9\-]/u', '', $value);

        return trim(preg_replace('/-+/', '-', $value), '-');
    }

    /**
     * Supported From Persian and Arabic Numbers to slug
    **/

    protected static function convertNumbers(string $string): string
    {
        $numbers = [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'
        ];
        return strtr($string, $numbers);
    }

}
