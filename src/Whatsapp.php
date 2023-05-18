<?php

namespace NotificationChannels\Whatsapp;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use NotificationChannels\Whatsapp\Exceptions\CouldNotSendNotification;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Whatsapp.
 */
class Whatsapp
{
    public static $apiServer;

    /** @var HttpClient HTTP Client */
    protected $http;

    protected $whatsappSession;

    protected $configMethods;

    protected $sessionFieldName;

    protected $apiKey;

    protected $bearerToken;

    /** @var string Whatsapp Bot API Base URI */
    protected $apiBaseUri;

    public function __construct(string $whatsappSession = null, HttpClient $httpClient = null, string $apiBaseUri = null, array $configMapMethods = [])
    {
        $this->whatsappSession = $whatsappSession ?? config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSession');
        $this->http = $httpClient ?? app()->make(HttpClient::class);
        $this->setApiBaseUri($apiBaseUri ?? config('whatsapp-notification-channel.services.whatsapp-bot-api.base_uri') ?? 'http://localhost:3000');
        $this->configMethods = $configMapMethods ?: config('whatsapp-notification-channel.services.whatsapp-bot-api.mapMethods') ?: [];
        $this->sessionFieldName = config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSessionFieldName');
        $this->apiKey = config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappApiKey');
        $this->bearerToken = config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappBearerToken');

        if ($this->configMethods) {
            return;
        }

        switch (self::$apiServer) {
            case 'wppconnect-server':
                $this->configMethods = [
                    'sendMessage' => 'send-message',
                    'sendDocument' => 'send-file-base64',
                    'sendFile' => 'send-file-base64',
                    'sendFile64' => 'send-file-base64',
                    'sendVideo' => 'send-file-base64',
                    'sendAnimation' => 'send-image',
                    'sendPhoto' => 'send-image',
                    'sendImage' => 'send-image',
                    'sendAudio' => 'send-voice-base64',
                    'sendLocation' => 'send-location',
                    'sendContact' => 'contact-vcard',
                ];
                break;
            case 'whatsapp-http-api':
                $this->configMethods = [
                    'sendMessage' => 'sendText',
                    'sendDocument' => 'sendFile',
                    'sendFile' => 'sendFile',
                    'sendFile64' => 'sendFile',
                    'sendVideo' => 'sendFile',
                    'sendAnimation' => 'sendImage',
                    'sendPhoto' => 'sendImage',
                    'sendAudio' => 'sendVoice',
                    'sendContact' => 'sendContactVcard',
                ];
                break;
            default:
                $this->configMethods = [
                    'sendMessage' => 'sendText',
                    'sendDocument' => 'sendFile',
                    'sendVideo' => 'sendFile',
                    'sendAnimation' => 'sendFile',
                    'sendPhoto' => 'sendFile',
                ];
                break;
        }
    }

    /**
     * Session getter.
     */
    public function getWhatsappSession(): ?string
    {
        return $this->whatsappSession;
    }

    /**
     * Whatsapp setter.
     *
     * @return $this
     */
    public function setWhatsappSession(string $whatsappSession): self
    {
        $this->whatsappSession = $whatsappSession;

        return $this;
    }

    /**
     * API Base URI getter.
     */
    public function getApiBaseUri(): string
    {
        return $this->apiBaseUri;
    }

    /**
     * API Base URI setter.
     *
     * @return $this
     */
    public function setApiBaseUri(string $apiBaseUri): self
    {
        $this->apiBaseUri = rtrim($apiBaseUri, '/');

        return $this;
    }

    /**
     * Set HTTP Client.
     *
     * @return $this
     */
    public function setHttpClient(HttpClient $http): self
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Get HttpClient.
     */
    protected function httpClient(): HttpClient
    {
        return $this->http;
    }


    public function sendMessage(array $params): ?ResponseInterface
    {
        return $this->sendRequest($this->configMethods['sendMessage'] ?? 'sendMessage', $params);
    }

    /**
     * Send File as Image or Document.
     *
     * @throws CouldNotSendNotification
     */
    public function sendFile(array $params, string $type = 'file', bool $multipart = false): ?ResponseInterface
    {
        $method = 'send'.Str::studly($type);
        return $this->sendRequest($this->configMethods[$method] ?? $method, $params, $multipart);
    }

    /**
     * Send a Poll.
     *
     * @throws CouldNotSendNotification
     */
    public function sendList(array $params): ?ResponseInterface
    {
        return $this->sendRequest($this->configMethods['sendList'] ?? 'sendList', $params);
    }

    /**
     * Send a Poll.
     *
     * @deprecated Use sendList(array $params): ?ResponseInterface.
     * @throws CouldNotSendNotification
     */
    public function sendLis(array $params): ?ResponseInterface
    {
        return $this->sendList($params);
    }

    /**
     * Send a Contact.
     *
     * @throws CouldNotSendNotification
     */
    public function sendContact(array $params): ?ResponseInterface
    {
        return $this->sendRequest($this->configMethods['sendContact'] ?? 'sendContact', $params);
    }

    /**
     * Send a Location.
     *
     * @throws CouldNotSendNotification
     */
    public function sendLocation(array $params): ?ResponseInterface
    {
        return $this->sendRequest($this->configMethods['sendLocation'] ?? 'sendLocation', $params);
    }

    /**
     * Send a message.
     *
     * @var WhatsappMessage|WhatsappFile|WhatsappLocation|WhatsappContact $message
     *
     * @throws CouldNotSendNotification
     */
    public function send($message): ?ResponseInterface
    {
        if ($message->toNotGiven()) {
            return null;
        }

        if ($message->hasWhatsappSession()) {
            $this->setWhatsappSession($message->whatsappSession);
        }

        $params = $message->toArray();

        $sendMethod = str_replace('Whatsapp', 'send', array_reverse(explode('\\', get_class($message)))[0]);

        if ($message instanceof WhatsappMessage) {
            if ($message->shouldChunk()) {
                $replyMarkup = $message->getPayloadValue('reply_markup');

                if ($replyMarkup) {
                    unset($params['reply_markup']);
                }

                $messages = $this->chunk($message->getPayloadValue($message->messageKey), $message->chunkSize);

                $payloads = collect($messages)->filter()->map(function ($text) use ($message, $params) {
                    return array_merge($params, [$message->messageKey => $text]);
                });

                if ($replyMarkup) {
                    $lastMessage = $payloads->pop();
                    $lastMessage['reply_markup'] = $replyMarkup;
                    $payloads->push($lastMessage);
                }

                return $payloads->map(function ($payload) {
                    $response = $this->sendMessage($payload);

                    // To avoid rate limit of one message per second.
                    sleep(1);

                    if ($response) {
                        return json_decode($response->getBody()->getContents(), true);
                    }

                    return $response;
                })->toArray();
            }

            return $this->sendMessage($params);
        } elseif ($message instanceof WhatsappFile) {
            return $this->sendFile($params, $message->type, $message->hasFile());
        } elseif (method_exists($this, $sendMethod)) {
            return $this->{$sendMethod}($params);
        } else {
            return null;
        }
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

    /**
     * Send an API request and return response.
     *
     * @throws CouldNotSendNotification
     */
    protected function sendRequest(string $endpoint, array $params, bool $multipart = false): ?ResponseInterface
    {
        if (blank($this->whatsappSession)) {
            throw CouldNotSendNotification::whatsappBotWhatsappSessionNotProvided('You must provide your whatsapp session to make any API requests.');
        }

        $apiUri = sprintf('%s/%s', $this->apiBaseUri, $endpoint);
        $params[$this->sessionFieldName] = $this->whatsappSession;
        try {
            return $this->httpClient()->post($apiUri, [
                $multipart ? 'multipart' : 'form_params' => $params,
                'headers' => array_merge(
                    ['Accept' => 'application/json'],
                    $this->apiKey ? ['X-Api-Key' => $this->apiKey] : [],
                    $this->bearerToken ? ['Authorization' => 'Bearer ' . $this->bearerToken] : [],
                )
            ]);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithWhatsapp($exception);
        }
    }
}
