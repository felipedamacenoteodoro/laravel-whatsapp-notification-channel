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

    public function boot()
    {
        $this->loadConfigs();

        $this->publishes([
            $this->basePath('config') => config_path()
        ], 'whatsapp-notification-channel-config');
    }


    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Whatsapp::class, static function () {
            return new Whatsapp(
                config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSession'),
                app(HttpClient::class),
                config('whatsapp-notification-channel.services.whatsapp-bot-api.base_uri'),
                config('whatsapp-notification-channel.services.whatsapp-bot-api.mapMethods')
            );
        });

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsapp', static function ($app) {
                return $app->make(WhatsappChannel::class);
            });
        });
    }

    protected function loadConfigs()
    {
        $this->mergeConfigFrom($this->basePath('config/whatsapp-notification-channel/services.php'), 'whatsapp-notification-channel.services');
    }

    protected function basePath($path = '')
    {
        return __DIR__ . '/../' . $path;
    }
}
