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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 125);
            $table->string('email',125)->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->string('city',125)->nullable();
            $table->string('province',125)->nullable();
            $table->string('phone',20)->nullable();
            $table->integer('postal_code')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
