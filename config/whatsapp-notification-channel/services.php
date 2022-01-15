<?php

return [

    'whatsapp-bot-api' => [
        'whatsappSession' => env('WHATSAPP_API_SESSION', ''),
        'base_uri' => env('WHATSAPP_API_BASE_URL', ''),
        'mapMethods' => [
            'sendMessage' => 'sendText',
            'sendDocument' => 'sendFile',
        ]
    ],
];
