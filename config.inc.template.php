<?php
return [
    'api' => [
        'key' => 'your_private_account_api_key',
        'url' => 'https://sensor.vrok.de/',
    ],
    'uart' => [
        'device' => '/dev/ttyUSB0',
    ],
    'notifications' => [
        'timestamp_file' => __DIR__.'/last_notification.txt',
        'power_pin'      => 7,
    ],
];