<?php

return [
    'plans' => [
        'start' => [
            'equipment_limit' => 200,
            'monthly_price' => 549.00,
        ],
        'pro' => [
            'equipment_limit' => 1000,
            'monthly_price' => 1290.00,
        ],
        'enterprise' => [
            'equipment_limit' => null,
            'monthly_price' => null,
        ],
    ],

    'trial' => [
        'warning_days' => [7, 3, 1],
    ],

    'ticket' => [
        'priorities' => ['low', 'medium', 'high', 'critical'],
        'origins' => ['manual', 'monitoring', 'customer_portal'],
    ],

    'meter_read_sources' => ['api', 'webhook', 'manual'],

    'billing' => [
        'default_due_days' => 10,
        'reference_format' => 'Ym',
        'anomaly_threshold_multiplier' => 1.30,
        'banks' => [
            'bb' => 'Banco do Brasil',
            'itau' => 'Itau',
            'bradesco' => 'Bradesco',
            'santander' => 'Santander',
            'caixa' => 'Caixa Economica Federal',
            'inter' => 'Inter',
            'sicredi' => 'Sicredi',
            'sicoob' => 'Sicoob',
            'c6' => 'C6 Bank',
            'outro' => 'Outro banco',
        ],
    ],
];
