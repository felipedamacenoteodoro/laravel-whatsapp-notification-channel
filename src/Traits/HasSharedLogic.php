<?php

namespace NotificationChannels\Whatsapp\Traits;

/**
 * Trait HasSharedLogic.
 */
trait HasSharedLogic
{
    /** @var string Bot whatsappSession. */
    public $whatsappSession;
    /** @var array Params payload. */
    protected $payload = [];

    /** @var array Inline Keyboard Buttons. */
    protected $buttons = [];

    /**
     * Recipient's Number.
     *
     * @param int|string $number
     *
     * @return $this
     */
    public function to($number): self
    {
        $this->payload['number'] = $number;
        $this->payload['phone'] = $number;
        $this->payload['chatId'] = $number;

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
        return !isset($this->payload['number']);
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
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
