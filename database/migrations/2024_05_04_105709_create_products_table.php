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
            $table->integer('stone_weight');
            $table->integer('product_net_weight');
            $table->integer('product_total_weight');
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
