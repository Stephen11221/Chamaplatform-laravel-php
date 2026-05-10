<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investments')) {
            Schema::create('investments', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('amount_invested', 12, 2);
                $table->decimal('expected_return', 12, 2)->nullable();
                $table->decimal('profit', 12, 2)->default(0);
                $table->string('investment_type');
                $table->enum('status', ['pending', 'approved', 'active', 'completed', 'rejected'])->default('pending');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
