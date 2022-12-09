<?php

return [
    'collections' => [
        'pages',
        'people' => [
            'extends' => '_layouts.main',
        ],
        'posts' => [
            'extends' => '_layouts.main',
            'section' => 'body',
        ],
    ],
];
