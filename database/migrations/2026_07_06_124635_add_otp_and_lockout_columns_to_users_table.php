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
            if (!Schema::hasColumn('users', 'login_with_otp')) {
                $table->boolean('login_with_otp')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'otp_login_valid_until')) {
                $table->timestamp('otp_login_valid_until')->nullable()->after('login_with_otp');
            }
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('otp_login_valid_until');
            }
            if (!Schema::hasColumn('users', 'account_blocked_until')) {
                $table->timestamp('account_blocked_until')->nullable()->after('failed_login_attempts');
            }
            if (!Schema::hasColumn('users', 'has_been_blocked_once')) {
                $table->boolean('has_been_blocked_once')->default(false)->after('account_blocked_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'login_with_otp')) {
                $table->dropColumn('login_with_otp');
            }
            if (Schema::hasColumn('users', 'otp_login_valid_until')) {
                $table->dropColumn('otp_login_valid_until');
            }
            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->dropColumn('failed_login_attempts');
            }
            if (Schema::hasColumn('users', 'account_blocked_until')) {
                $table->dropColumn('account_blocked_until');
            }
            if (Schema::hasColumn('users', 'has_been_blocked_once')) {
                $table->dropColumn('has_been_blocked_once');
            }
        });
    }
};
