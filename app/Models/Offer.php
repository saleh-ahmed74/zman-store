<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = ['product_id', 'pieces', 'discount_percent', 'free_shipping', 'is_active'];

    protected function casts(): array
    {
        return ['discount_percent' => 'decimal:2', 'free_shipping' => 'boolean', 'is_active' => 'boolean'];
    }
}
