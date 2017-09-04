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
    'display' => [
        'device' => 'fb1',
    ],
    'gpio_watch' => [
        22 => [
            'pull_up' => true,
            'watch_for' => 0,
            'callback' => function(RpiSensor\GpioWatch $gpioWatch) {
                $gpioWatch->statusDisplay->update();
            }
        ],
    ],
];