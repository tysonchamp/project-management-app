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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('service_name');
            $table->string('username')->nullable();
            $table->text('password'); // Will be encrypted
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('credential_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credential_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('credential_id')->references('id')->on('credentials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credential_user');
        Schema::dropIfExists('credentials');
    }
};
