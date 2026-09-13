<?php

declare(strict_types=1);

return [
    'simple_in_stock' => [
        'sku' => 'JP-FIXTURE-001',
        'name' => 'Fixture Product 100 ml',
        'regular_price' => '49.900',
        'stock_quantity' => 10,
        'stock_status' => 'instock',
    ],
    'simple_out_of_stock' => [
        'sku' => 'JP-FIXTURE-002',
        'name' => 'Fixture Product 50 ml',
        'regular_price' => '29.900',
        'stock_quantity' => 0,
        'stock_status' => 'outofstock',
    ],
];
