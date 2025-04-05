<?php

use Illuminate\Support\Str;
trait HasSlug
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function HasSlug()
    {
        static::saving(function ($model) {
            $model->generateSlug();
        });
    }

    /**
     * Generate the slug from the specified field.
     *
     * @return void
     */
    public function generateSlug(): void
    {
        $slugSource = $this->slugSourceField ?? 'title'; // Default to 'title' if not defined
        $slugDestination = $this->slugDestinationField ?? 'slug'; // Default to 'slug' if not defined

        // Generate the slug and assign it to the destination field
        $this->{$slugDestination} = Str::slug($this->{$slugSource});
    }
}
