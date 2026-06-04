<?php

return [
    'default_template' => 'restaurant1',

    'templates' => [
        'restaurant1' => [
            'label' => 'مطعم داكن فاخر',
            'description' => 'قالب داكن أنيق مناسب للمطاعم والكافيهات التي تريد مظهراً فخماً.',
            'view' => 'templates.website.dark.restaurant1',
            'preview' => 'templates/previews/restaurant1-long.png',
            'type' => 'website',
        ],
        'restaurant2' => [
            'label' => 'مطعم بسيط فاتح',
            'description' => 'قالب خفيف وعملي مناسب للمطاعم والكافيهات اليومية.',
            'view' => 'templates.website.both.restaurant2',
            'preview' => 'templates/previews/restaurant2-long.png',
            'type' => 'website',
        ],
        'restaurant3' => [
            'label' => 'منيو QR حديث',
            'description' => 'قالب عملي ومباشر مناسب للكافيهات والمطاعم التي تريد عرض المنيو بسرعة عبر QR.',
            'view' => 'templates.website.both.restaurant3',
            'preview' => 'templates/previews/restaurant3-long.png',
            'type' => 'website',
        ],
    ],
];