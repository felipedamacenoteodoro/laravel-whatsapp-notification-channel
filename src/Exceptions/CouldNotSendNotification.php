<?php

namespace NotificationChannels\Whatsapp\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Class CouldNotSendNotification.
 */
class CouldNotSendNotification extends Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @param ClientException|ServerException $exception
     *
     * @return static
     */
    public static function whatsappRespondedWithAnError($exception): self
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
     * @param \Throwable $exception
     *
     * @return static
     */
    public static function couldNotCommunicateWithWhatsapp($exception): self
    {
        if (is_a($exception, ClientException::class) || is_a($exception, ServerException::class)) {
            return self::whatsappRespondedWithAnError($exception);
        }

        $message = $exception->getMessage();

        return new static("The communication with Whatsapp api failed. `{$message}`", 0, $exception);
    }
}
