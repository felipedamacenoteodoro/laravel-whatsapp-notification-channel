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
    /** @var HttpClient HTTP Client */
    protected $http;

    protected $whatsappSession;

    protected $configMethods;

    /** @var string Whatsapp Bot API Base URI */
    protected $apiBaseUri;

    public function __construct(string $whatsappSession = null, HttpClient $httpClient = null, string $apiBaseUri = null, array $configMapMethods = [])
    {
        $this->whatsappSession = $whatsappSession ?? config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSession');
        $this->http = $httpClient ?? new HttpClient();
        $this->setApiBaseUri($apiBaseUri ?? config('whatsapp-notification-channel.services.whatsapp-bot-api.base_uri') ?? 'http://localhost:3000');
        $this->configMethods = $configMapMethods ?: config('whatsapp-notification-channel.services.whatsapp-bot-api.mapMethods') ?: [];
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


    public function sendMessage(array $params): ?ResponseInterface
    {
        return $this->sendRequest($this->configMethods['sendMessage'] ?? 'sendMessage', $params);
    }

    /**
     * Send File as Image or Document.
     *
     * @throws CouldNotSendNotification
     */
    public function sendFile(array $params, string $type, bool $multipart = false): ?ResponseInterface
    {
        //  dd('send'.Str::studly($type), $params);
        return $this->sendRequest($this->configMethods['send'.Str::studly($type)] ?? 'send'.Str::studly($type), $params, $multipart);
    }

    /**
     * Send a Poll.
     *
     * @throws CouldNotSendNotification
     */
    public function sendList(array $params): ?ResponseInterface
    {
        return $this->sendRequest('sendList', $params);
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
     * Get HttpClient.
     */
    protected function httpClient(): HttpClient
    {
        return $this->http;
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
        try {
            $params[config('whatsapp-notification-channel.services.whatsapp-bot-api.whatsappSessionFieldName')] = $this->whatsappSession;
            return $this->httpClient()->post($apiUri, [
                $multipart ? 'multipart' : 'form_params' => $params,
            ]);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::whatsappRespondedWithAnError($exception);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithWhatsapp($exception);
        }
    }
}
