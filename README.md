# Laravel Slugable

Laravel Slugable is a lightweight Laravel trait that automatically generates slugs from model fields like `title`, `name`, or any custom source — and stores it in a customizable destination field such as `slug`, etc.

Perfect for blogs, e-commerce, CMS, or any app that needs clean, readable, SEO-friendly URLs.

# Features

- Auto-generate slug on model creation

- Optional re-generation on model update

- Customizable source and destination fields

- No external dependencies

- Support for Persian and Arabic Numbers to slug

# Installation

Install Package:

```bash
composer require rayiumir/laravel-slugable
```

After Publish Files:

```bash
php artisan vendor:publish --provider="Rayiumir\\Slugable\\ServiceProvider\\SlugableServiceProvider"
```

# How to use

Calling `HasSlugable` in Models `Post.php`.

```
class Post extends Model
{
    use HasSlugable;
}
```

Provided that the `title` and `slug` fields are in the database.

If you want to use a custom field for slug generation, you can easily do that:

```
class Post extends Model
{
    use HasSlugable;

    protected $slugSourceField = 'name';
    protected $slugDestinationField = 'slug';
}
```

# Example

```
$post = new Post();
$post->title = 'Laravel 12';
$post->save();

echo $post->slug; // Output: laravel-12
```


