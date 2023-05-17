<?php

return [

    'whatsapp-bot-api' => [
        'whatsappSessionFieldName' => env('WHATSAPP_API_SESSION_FIELD_NAME', ''),
        'whatsappSession' => env('WHATSAPP_API_SESSION', ''),
        'whatsappApiKey' => env('WHATSAPP_API_KEY'),
        'whatsappBearerToken' => env('WHATSAPP_BEARER_TOKEN'),
        'base_uri' => env('WHATSAPP_API_BASE_URL', ''),
        'mapMethods' => [
            'sendMessage' => 'sendText',
            'sendDocument' => 'sendFile',
        ]
    ],
];
