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
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2);
            // $table->decimal('discount', 10, 2)->default(0);
            $table->integer('stock');
            $table->integer('weight')->default(0);
            $table->enum('status', ['available', 'unavailable'])->default('unavailable');
            // $table->boolean('has_variants')->default(false);
            $table->integer('purchased_quantity')->default(0);
            $table->string('view_count')->default(0);
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
