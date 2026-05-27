<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('department', 120)->nullable()->after('location');
            $table->boolean('is_backup')->default(false)->after('department');

            $table->index(['tenant_id', 'is_backup']);
            $table->index(['tenant_id', 'department']);
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_backup']);
            $table->dropIndex(['tenant_id', 'department']);
            $table->dropColumn(['department', 'is_backup']);
        });
    }
};
