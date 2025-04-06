<?php

use Illuminate\Database\Eloquent\Model;
use Rayiumir\Slugable\Traits\HasSlugable;

class HasSlugTest
{
    public function test_slug_is_generated_automatically(): void
    {
        $post = new class extends Model {
            use HasSlugable;

            protected $table = 'posts';
            public $timestamps = false;

            protected $fillable = ['title', 'slug'];
        };

        $post->title = 'hello world!';
        $post->save();

        $this->assertEquals('hello-world!', $post->slug);
    }
}
