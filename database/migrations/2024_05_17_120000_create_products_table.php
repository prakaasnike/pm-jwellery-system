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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_image');
            $table->string('name');
            $table->string('stone_name')->nullable();
            $table->float('stone_weight')->nullable();
            $table->float('product_net_weight')->nullable();
            $table->float('product_total_weight')->nullable();
            $table->foreignId('unit_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
