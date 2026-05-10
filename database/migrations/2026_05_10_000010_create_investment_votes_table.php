<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('vote', ['approve', 'reject', 'abstain']);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['investment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_votes');
    }
};
