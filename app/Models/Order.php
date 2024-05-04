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
        'customer_id',
        'product_id',
        'status_id',
        'payment_id',
        'Received_date',
        'Delivery_date',
    ];

    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'product_id' => 'integer',
        'status_id' => 'integer',
        'payment_id' => 'integer',
        'Received_date' => 'date',
        'Delivery_date' => 'date',
    ];

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
