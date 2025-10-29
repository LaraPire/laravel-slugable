# Laravel Slugable

Laravel Slugable is a lightweight Laravel trait that automatically generates slugs from model fields like `title`, `name`, or any custom source — and stores it in a customizable destination field such as `slug`, etc.

Perfect for blogs, e-commerce, CMS, or any app that needs clean, readable, SEO-friendly URLs with multi-language support.

## Features

- 🚀 Auto-generate slug on model creation
- 🔄 Optional re-generation on model update
- 🛠️ Customizable source and destination fields
- 🌍 Multi-language support (Persian, Arabic, English)
- 🔢 Automatic conversion of non-English numbers
- 🧹 Special character cleaning for each language
- 🔍 Unique slug enforcement with counter
- 📏 Max length enforcement
- 💡 No external dependencies
- ⚡ Static helper method for non-model usage
- 🧵 Thread-safe implementation
- 🔒 Type-safe operations

## Installation

Install Package:

```bash
composer require rayiumir/laravel-slugable
```

After Publish Files:

```bash
php artisan vendor:publish --provider="Rayiumir\\Slugable\\ServiceProvider\\SlugableServiceProvider"
```

## Basic Usage

Calling `HasSlugable` in Models `Post.php`:

```php
class Post extends Model
{
    use HasSlugable;
}
```

Provided that the `title` and `slug` fields are in the database.

## Advanced Configuration

### Custom Field Names

```php
class Post extends Model
{
    use HasSlugable;

    protected $slugSourceField = 'name';       // Field to generate slug from
    protected $slugDestinationField = 'slug';  // Field to store slug in
}
```

### Language Support

```php
class Post extends Model
{
    use HasSlugable;

    protected $slugLanguage = 'fa'; // Supports 'fa', 'ar', 'en'
}
```

### Other Options

```php
class Post extends Model
{
    use HasSlugable;

    protected $slugSeparator = '_';      // Default: '-'
    protected $slugMaxLength = 100;      // Default: 250
    protected $slugForceUpdate = true;   // Force regenerate on update
    protected $slugShouldBeUnique = false; // Disable unique enforcement
}
```

## Static Usage

You can generate slugs without a model instance:

```php
$slug = Post::generateSlugFrom('My Post Title', [
    'language' => 'en',
    'separator' => '_',
    'maxLength' => 50
]);
```

## Using the `id` parameter

If in a resource route, the absence of the id parameter causes a 404 error, it's enough to add the following code to the route:

```
// web.php
Route::resource('posts', PostController::class)
    ->parameters(['posts' => 'post:id']); // Add this parameter binding
```

## Example Workflow

```php
// Create with auto-slug
$post = new Post();
$post->title = 'Laravel ۱۲'; // Persian numbers
$post->save();

echo $post->slug; // Output: laravel-12

// Force update slug
$post->slugForceUpdate = true;
$post->title = 'New Laravel ۱۲';
$post->save();

echo $post->slug; // Output: new-laravel-12

// Generate slug without saving
$slug = Post::generateSlugFrom('Custom Title');
```

## Language-Specific Handling

The trait automatically handles:
- Persian/Arabic numbers conversion
- ZWNJ (Zero-width non-joiner) removal for Persian
- Tatweel removal for Arabic/Persian
- Language-specific character preservation

## Best Practices

1. Add index to your slug column for better performance:
```php
Schema::table('posts', function (Blueprint $table) {
    $table->string('slug')->unique()->index();
});
```

2. For large tables, consider adding the slug generation in a migration:

```php
Post::chunk(200, function ($posts) {
    $posts->each->generateSlug();
});
```

3. Use the static method when generating slugs in migrations:

```php
$posts->each(function ($post) {
    $post->slug = Post::generateSlugFrom($post->title);
    $post->save();
});
```

## Performance Notes

- The trait uses efficient string operations
- Language patterns are defined as constants for better performance
- Slug uniqueness check is optimized to exclude current model
- Works with soft-deleted models


