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

            $table->string('user_id')->unique();
            $table->enum('role', ['admin', 'user', 'staff', 'medical']);
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->enum('gender', ['Male', 'Female']);
            $table->date('dob')->nullable();
            $table->integer('created_by')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('forgot_password')->nullable();
            $table->rememberToken();
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
