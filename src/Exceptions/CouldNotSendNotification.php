<?php

namespace NotificationChannels\Whatsapp\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;

/**
 * Class CouldNotSendNotification.
 */
class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @return static
     */
    public static function whatsappRespondedWithAnError(ClientException $exception): self
    {
        if (!$exception->hasResponse()) {
            return new static('Whatsapp api responded with an error but no response body found');
        }

        $statusCode = $exception->getResponse()->getStatusCode();

        $result = json_decode($exception->getResponse()->getBody()->getContents(), false);
        $result = $result->exception ?? $result;
        $description = $result->description ?? $result->message ?? 'no description given';

        return new static("Whatsapp api responded with an error `{$statusCode} - {$description}`", 0, $exception);
    }

    /**
     * Thrown when there's no whatsapp session provided.
     *
     * @return static
     */
    public static function whatsappBotWhatsappSessionNotProvided(string $message): self
    {
        return new static($message);
    }

    /**
     * Thrown when we're unable to communicate with Whatsapp.
     *
     * @param $message
     *
     * @return static
     */
    public static function couldNotCommunicateWithWhatsapp($message): self
    {
        return new static("The communication with Whatsapp api failed. `{$message}`");
    }
}
