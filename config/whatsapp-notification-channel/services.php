<?php

return [

    'whatsapp-bot-api' => [
        'whatsappSessionFieldName' => env('WHATSAPP_API_SESSION_FIELD_NAME', ''),
        'whatsappSession' => env('WHATSAPP_API_SESSION', ''),
        'base_uri' => env('WHATSAPP_API_BASE_URL', ''),
        'mapMethods' => [
            'sendMessage' => 'sendText',
            'sendDocument' => 'sendFile',
        ]
    ],
];
