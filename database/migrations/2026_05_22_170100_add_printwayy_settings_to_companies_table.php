<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('printwayy_enabled')->default(false)->after('is_active');
            $table->string('printwayy_workspace_id')->nullable()->after('printwayy_enabled');
            $table->string('printwayy_api_base_url')->nullable()->after('printwayy_workspace_id');
            $table->text('printwayy_api_token')->nullable()->after('printwayy_api_base_url');
            $table->timestamp('printwayy_last_sync_at')->nullable()->after('printwayy_api_token');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'printwayy_enabled',
                'printwayy_workspace_id',
                'printwayy_api_base_url',
                'printwayy_api_token',
                'printwayy_last_sync_at',
            ]);
        });
    }
};

