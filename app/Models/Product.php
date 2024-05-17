<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_image',
        'name',
        'stone_name',
        'stone_weight',
        'product_net_weight',
        'product_total_weight',
        'unit_id',
        'category_id',
        'type_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'stone_weight' => 'float',
        'product_net_weight' => 'float',
        'product_total_weight' => 'float',
        'unit_id' => 'integer',
        'category_id' => 'integer',
        'type_id' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class);
    }
}
