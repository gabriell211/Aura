<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('payment_method', 40)->default('cobranca_bancaria')->after('status');
            $table->string('reading_period', 20)->default('tarde')->after('payment_method');
            $table->unsignedTinyInteger('reading_fixed_day')->nullable()->after('reading_period');
            $table->date('reading_start_date')->nullable()->after('reading_fixed_day');
            $table->date('reading_end_date')->nullable()->after('reading_start_date');
            $table->unsignedSmallInteger('due_days')->default(15)->after('reading_end_date');
            $table->string('print_type', 60)->default('suporte_setor')->after('due_days');
            $table->string('counter_display_mode', 30)->default('pt_color')->after('print_type');
            $table->boolean('allow_extension')->default(false)->after('counter_display_mode');
            $table->boolean('show_observation')->default(false)->after('allow_extension');
            $table->boolean('issue_boleto')->default(true)->after('show_observation');
            $table->boolean('unified_boleto')->default(false)->after('issue_boleto');
            $table->boolean('unified_contract')->default(false)->after('unified_boleto');
            $table->string('external_contract_number', 100)->nullable()->after('unified_contract');
            $table->decimal('global_bw_franchise_value', 12, 2)->default(0)->after('external_contract_number');
            $table->decimal('global_color_franchise_value', 12, 2)->default(0)->after('global_bw_franchise_value');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'reading_period',
                'reading_fixed_day',
                'reading_start_date',
                'reading_end_date',
                'due_days',
                'print_type',
                'counter_display_mode',
                'allow_extension',
                'show_observation',
                'issue_boleto',
                'unified_boleto',
                'unified_contract',
                'external_contract_number',
                'global_bw_franchise_value',
                'global_color_franchise_value',
            ]);
        });
    }
};
