<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'price',
        'tax_percentage',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeLowStock(Builder $query, int $threshold): Builder
    {
        return $query->where('stock', '<', $threshold);
    }
}
