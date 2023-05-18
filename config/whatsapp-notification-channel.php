<?php

return [
  'services'=>[
    'whatsapp-bot-api' => [
        'whatsappApiServer' => env('WHATSAPP_API_SERVER'), // wppconnect-server, whatsapp-http-api, default
        'whatsappSessionFieldName' => env('WHATSAPP_API_SESSION_FIELD_NAME', 'session'),
        'whatsappSession' => env('WHATSAPP_API_SESSION', 'default'),
        'whatsappApiKey' => env('WHATSAPP_API_KEY'),
        'whatsappBearerToken' => env('WHATSAPP_BEARER_TOKEN'),
        'base_uri' => env('WHATSAPP_API_BASE_URL', ''),
        'mapMethods' => [], // 'sendMessage' => 'sendText', 'sendDocument' => 'sendFile',
    ],
  ]
];
