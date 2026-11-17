<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePage extends Model
{
    protected $table = 'pages';

    protected $fillable = ['slug', 'title_ar', 'title_en', 'body_ar', 'body_en', 'image_path'];
}
