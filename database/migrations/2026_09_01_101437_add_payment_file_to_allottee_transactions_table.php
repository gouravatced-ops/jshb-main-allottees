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
        Schema::table('allottee_transactions', function (Blueprint $table) {
            $table->string('payment_file_name')->nullable()->after('receipt_path');
            $table->string('payment_file_path')->nullable()->after('payment_file_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allottee_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_file_name', 'payment_file_path']);
        });
    }
};
