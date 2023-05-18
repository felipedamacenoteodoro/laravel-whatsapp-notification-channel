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
        Whatsapp::$apiServer = $this->app['config']->get('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappApiServer');

        $this->app->singleton(Whatsapp::class);

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('whatsapp', static function ($app) {
                return $app->make(WhatsappChannel::class);
            });
        });
    }

    protected function loadConfigs()
    {
        $this->mergeConfigFrom($this->basePath('config/whatsapp-notification-channel.php'), 'whatsapp-notification-channel');
    }

    protected function basePath($path = '')
    {
        return __DIR__ . '/../' . $path;
    }
}
