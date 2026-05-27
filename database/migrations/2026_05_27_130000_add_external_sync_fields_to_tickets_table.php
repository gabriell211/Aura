<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('external_source', 40)->nullable()->after('origin');
            $table->string('external_reference', 191)->nullable()->after('external_source');
            $table->string('external_payload_hash', 64)->nullable()->after('external_reference');
            $table->timestamp('external_last_synced_at')->nullable()->after('external_payload_hash');

            $table->index(['tenant_id', 'external_source', 'external_reference'], 'tickets_external_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_external_lookup_idx');
            $table->dropColumn([
                'external_source',
                'external_reference',
                'external_payload_hash',
                'external_last_synced_at',
            ]);
        });
    }
};
