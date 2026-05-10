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
        Schema::table('users', function (Blueprint $table) {
            //$table->string('full_name')->after('id');
           // $table->string('national_id')->unique()->after('full_name');
        ///    $table->string('phone_number')->after('national_id');
//$table->string('location')->nullable()->after('phone_number');
          //  $table->string('profile_photo')->nullable()->after('location');
        //    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('remember_token');
            /////////$table->timestamp('last_login_at')->nullable()->after('status');
//            $table->softDeletes()->after('last_login_at');
//            $table->index(['email', 'phone_number'])->after('softDeletes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'phone_number']);
            $table->dropSoftDeletes();
            $table->dropColumn(['full_name', 'national_id', 'phone_number', 'location', 'profile_photo', 'status', 'last_login_at']);
        });
    }
};