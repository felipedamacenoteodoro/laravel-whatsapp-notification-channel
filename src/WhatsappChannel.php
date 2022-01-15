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
    public function send($notifiable, Notification $notification): ?array
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

        if ($message->hasToken()) {
            $this->whatsapp->setToken($message->token);
        }

        $params = $message->toArray();

        $sendMethod = str_replace('Whatsapp', 'send', array_reverse(explode('\\', get_class($message)))[0]);

        try {
            if ($message instanceof WhatsappMessage) {
                if ($message->shouldChunk()) {
                    $replyMarkup = $message->getPayloadValue('reply_markup');

                    if ($replyMarkup) {
                        unset($params['reply_markup']);
                    }

                    $messages = $this->chunk($message->getPayloadValue('text'), $message->chunkSize);

                    $payloads = collect($messages)->filter()->map(function ($text) use ($params) {
                        return array_merge($params, ['text' => $text]);
                    });

                    if ($replyMarkup) {
                        $lastMessage = $payloads->pop();
                        $lastMessage['reply_markup'] = $replyMarkup;
                        $payloads->push($lastMessage);
                    }

                    return $payloads->map(function ($payload) {
                        $response = $this->whatsapp->sendMessage($payload);

                        // To avoid rate limit of one message per second.
                        sleep(1);

                        if ($response) {
                            return json_decode($response->getBody()->getContents(), true);
                        }

                        return $response;
                    })->toArray();
                }

                $response = $this->whatsapp->sendMessage($params);
            } elseif ($message instanceof WhatsappFile) {
                $response = $this->whatsapp->sendFile($params, $message->type, $message->hasFile());
            } elseif (method_exists($this->whatsapp, $sendMethod)) {
                $response = $this->whatsapp->{$sendMethod}($params);
            } else {
                return null;
            }
        } catch (CouldNotSendNotification $exception) {
            $this->dispatcher->dispatch(new NotificationFailed(
                $notifiable,
                $notification,
                'whatsapp',
                []
            ));

            throw $exception;
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Chunk the given string into an array of strings.
     */
    public function chunk(string $value, int $limit = 4096): array
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return [$value];
        }

        if ($limit >= 4097) {
            $limit = 4096;
        }

        $output = explode('%#TGMSG#%', wordwrap($value, $limit, '%#TGMSG#%'));

        // Fallback for when the string is too long and wordwrap doesn't cut it.
        if (count($output) <= 1) {
            $output = mb_str_split($value, $limit, 'UTF-8');
        }

        return $output;
    }
}
