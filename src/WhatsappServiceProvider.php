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
                config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSession'),
                app(HttpClient::class),
                config('whatsapp-notification-channel.services.whatsapp-bot-api.base_uri')
            );
        });

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsapp', static function ($app) {
                return $app->make(WhatsappChannel::class);
            });
        });
    }

    public function boot()
    {
        $this->publishes([
            $this->basePath('config') => config_path()
        ], 'whatsapp-notification-channel-config');
    }
}
