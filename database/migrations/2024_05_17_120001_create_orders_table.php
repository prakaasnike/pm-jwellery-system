<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_name');
            $table->string('order_image');
            $table->foreignId('customer_id')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->enum('status', ["received","urgent","ongoing","delivered"]);
            $table->enum('payment_status', ["paid","unpaid","initialpaid"]);
            $table->date('received_date');
            $table->date('delivery_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
