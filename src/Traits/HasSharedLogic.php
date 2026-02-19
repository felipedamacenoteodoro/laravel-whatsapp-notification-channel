<?php

namespace NotificationChannels\Whatsapp\Traits;

use NotificationChannels\Whatsapp\Whatsapp;

/**
 * Trait HasSharedLogic.
 */
trait HasSharedLogic
{
    /** @var string Bot whatsappSession. */
    public $whatsappSession;

    /** @var string */
    public $numberKey;

    /** @var string */
    public $messageKey;

    /** @var array Params payload. */
    protected $payload = [];

    /** @var array Inline Keyboard Buttons. */
    protected $buttons = [];

    private function setNumberKey($numberKey = null)
    {
        $this->numberKey = $numberKey;

        if ($this->numberKey) {
            return;
        }

        switch (Whatsapp::$apiServer) {
            case 'wppconnect-server':
                $this->numberKey = 'phone';
                return;
            case 'whatsapp-http-api':
                $this->numberKey = 'chatId';
                return;
            default:
                $this->numberKey = 'number';
        }
    }

    private function setMessageKey($messageKey = null)
    {
        $this->messageKey = $messageKey;

        if ($this->messageKey) {
            return;
        }

        $this->messageKey = Whatsapp::$apiServer == 'wppconnect-server' ? 'message' : 'text';
    }
    /**
     * Recipient's Number.
     *
     * @param int|string $number
     *
     * @return $this
     */
    public function to($number): self
    {
        $this->payload[$this->numberKey] = $number;

        return $this;
    }

    /**
     * Whatsapp Session.
     * Overrides default whatsappSession with the given value for this notification.
     *
     * @return $this
     */
    public function whatsappSession(string $whatsappSession): self
    {
        $this->whatsappSession = $whatsappSession;

        return $this;
    }

    /**
     * Determine if whatsapp session is given for this notification.
     */
    public function hasWhatsappSession(): bool
    {
        return null !== $this->whatsappSession;
    }

    /**
     * Additional options to pass to sendMessage method.
     *
     * @return $this
     */
    public function options(array $options): self
    {
        $this->payload = array_merge($this->payload, $options);

        return $this;
    }

    /**
     * Determine if number is not given.
     */
    public function toNotGiven(): bool
    {
        return !isset($this->payload[$this->numberKey]);
    }

    /**
     * Get payload value for given key.
     *
     * @return null|mixed
     */
    public function getPayloadValue(string $key)
    {
        return $this->payload[$key] ?? null;
    }

    /**
     * Returns params payload.
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
