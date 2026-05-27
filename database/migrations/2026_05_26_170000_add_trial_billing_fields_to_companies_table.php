<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('trial_status', 30)->default('trialing')->after('trial_ends_at');
            $table->timestamp('trial_last_notice_at')->nullable()->after('trial_status');
            $table->string('trial_last_notice_stage', 20)->nullable()->after('trial_last_notice_at');
            $table->string('infinitepay_order_nsu', 120)->nullable()->after('trial_last_notice_stage');
            $table->string('infinitepay_checkout_slug', 120)->nullable()->after('infinitepay_order_nsu');
            $table->text('infinitepay_checkout_url')->nullable()->after('infinitepay_checkout_slug');
            $table->timestamp('infinitepay_checkout_generated_at')->nullable()->after('infinitepay_checkout_url');
            $table->timestamp('trial_converted_at')->nullable()->after('infinitepay_checkout_generated_at');
            $table->timestamp('trial_expired_at')->nullable()->after('trial_converted_at');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'trial_status',
                'trial_last_notice_at',
                'trial_last_notice_stage',
                'infinitepay_order_nsu',
                'infinitepay_checkout_slug',
                'infinitepay_checkout_url',
                'infinitepay_checkout_generated_at',
                'trial_converted_at',
                'trial_expired_at',
            ]);
        });
    }
};

