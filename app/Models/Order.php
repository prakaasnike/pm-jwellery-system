<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_name',
        'order_image',
        'customer_id',
        'product_id',
        'status',
        'payment_status',
        'received_date',
        'delivery_date',
    ];

    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'product_id' => 'integer',
        'received_date' => 'date',
        'delivery_date' => 'date',
        'order_image' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}
