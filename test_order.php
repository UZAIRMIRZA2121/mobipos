<?php
require __DIR__ . '/vendor/autoload.php';
\ = require_once __DIR__ . '/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Http\Kernel::class);
\ = \->handle(
    \ = Illuminate\Http\Request::create(
        '/shop/api/orders',
        'POST',
        [
            'buyer_id' => null,
            'subtotal' => 300000,
            'tax' => 0,
            'discount' => 0,
            'total' => 300000,
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => 1, 'qty' => 1, 'price' => 300000]
            ]
        ]
    )
);
echo \->getContent();

