<?php

namespace NotificationChannels\Whatsapp;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

/**
 * Class WhatsappServiceProvider.
 */
class WhatsappServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsApp::class, static function () {
            return new WhatsApp(
                config('services.whatsapp-bot-api.token'),
                app(HttpClient::class),
                config('services.whatsapp-bot-api.base_uri')
            );
        });

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsApp', static function ($app) {
                return $app->make(WhatsAppChannel::class);
            });
        });
    }
}
