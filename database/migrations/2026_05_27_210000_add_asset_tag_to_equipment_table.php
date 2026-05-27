<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('asset_tag', 120)->nullable()->after('serial_number');

            $table->index(['tenant_id', 'asset_tag']);
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'asset_tag']);
            $table->dropColumn('asset_tag');
        });
    }
};
