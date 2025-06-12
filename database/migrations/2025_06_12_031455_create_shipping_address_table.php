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
        Schema::create('shipping_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('label'); // misalnya: Rumah, Kantor
            $table->string('recipient_name');
            $table->string('phone');
            $table->unsignedInteger('province_id'); // dari RajaOngkir
            $table->string('province_name');
            $table->unsignedInteger('city_id'); // dari RajaOngkir
            $table->string('city_name');
            $table->text('full_address');
            $table->string('postal_code');
            $table->text('notes')->nullable(); // Add this line
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_address');
    }
};
