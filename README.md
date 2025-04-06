# Laravel Slugable

Laravel Slugable is a lightweight Laravel trait that automatically generates slugs from model fields like `title`, `name`, or any custom source — and stores it in a customizable destination field such as `slug`, etc.

Perfect for blogs, e-commerce, CMS, or any app that needs clean, readable, SEO-friendly URLs.

# Features

- Auto-generate slug on model creation

- Optional re-generation on model update

- Customizable source and destination fields

- No external dependencies

- Works out-of-the-box with Eloquent

-Support for Persian and Arabic languages ​​for slug translation

# Installation

Install Package:

```bash
composer require rayiumir/laravel-slugable
```

After Publish Files:

```bash
php artisan vendor:publish --provider="Rayiumir\\HasSlug\\ServiceProvider\\SlugServiceProvider"
```

# How to use

Calling `HasSlug` in Models `Post.php`.

```
class Post extends Model
{
    use HasSlug;
}
```

Provided that the `title` and `slug` fields are in the database.

# Example

```
$post = new Post();
$post->title = 'Laravel 12';
$post->save();

echo $post->slug; // Output: laravel-12
```

