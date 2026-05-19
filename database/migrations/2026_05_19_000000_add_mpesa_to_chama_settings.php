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
        Schema::table('chama_settings', function (Blueprint $table) {
            $table->string('mpesa_paybill')->nullable()->after('currency');
            $table->string('mpesa_account_name')->nullable()->after('mpesa_paybill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chama_settings', function (Blueprint $table) {
            $table->dropColumn(['mpesa_paybill', 'mpesa_account_name']);
        });
    }
};
