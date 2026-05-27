<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->string('lifecycle_stage', 40)->default('in_stock')->after('storage_location');
            $table->timestamp('last_movement_at')->nullable()->after('lifecycle_stage');
            $table->string('last_movement_type', 40)->nullable()->after('last_movement_at');
            $table->text('lifecycle_notes')->nullable()->after('last_movement_type');

            $table->index(['tenant_id', 'lifecycle_stage']);
            $table->index(['tenant_id', 'last_movement_at']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'lifecycle_stage']);
            $table->dropIndex(['tenant_id', 'last_movement_at']);
            $table->dropColumn(['lifecycle_stage', 'last_movement_at', 'last_movement_type', 'lifecycle_notes']);
        });
    }
};
