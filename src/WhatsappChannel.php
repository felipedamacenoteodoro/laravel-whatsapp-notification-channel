<?php

namespace NotificationChannels\Whatsapp;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Whatsapp\Exceptions\CouldNotSendNotification;
use NotificationChannels\Whatsapp\Whatsapp;

/**
 * Class WhatsappChannel.
 */
class WhatsappChannel
{
    /**
     * @var Whatsapp
     */
    protected $whatsapp;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * Channel constructor.
     */
    public function __construct(Whatsapp $whatsapp, Dispatcher $dispatcher)
    {
        $this->whatsapp = $whatsapp;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     *
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsapp($notifiable);

        if (is_string($message)) {
            $message = WhatsappMessage::create($message);
        }

        if ($message->toNotGiven()) {
            $to = $notifiable->routeNotificationFor('whatsapp', $notification)
                ?? $notifiable->routeNotificationFor(self::class, $notification);

            if (!$to) {
                return null;
            }

            $message->to($to);
        }

        try {
            $response = $this->whatsapp->send($message);
        } catch (CouldNotSendNotification $exception) {
            $this->dispatcher->dispatch(new NotificationFailed(
                $notifiable,
                $notification,
                'whatsapp',
                []
            ));

            throw $exception;
        }

        return $response ? json_decode($response->getBody()->getContents(), true) : null;
    }
}
