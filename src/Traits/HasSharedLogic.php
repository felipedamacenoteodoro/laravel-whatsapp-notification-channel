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
     * @param int|string $chatId
     *
     * @return $this
     */
    public function to($number): self
    {
        $this->payload['number'] = $number;

        return $this;
    }

    /**
     * Add an inline button.
     *
     * @return $this
     */
    public function button(string $text, string $url, int $columns = 2): self
    {
        $this->buttons[] = compact('text', 'url');

        $this->payload['reply_markup'] = json_encode([
            'inline_keyboard' => array_chunk($this->buttons, $columns),
        ]);

        return $this;
    }

    /**
     * Add an inline button with callback_data.
     *
     * @return $this
     */
    public function buttonWithCallback(string $text, string $callback_data, int $columns = 2): self
    {
        $this->buttons[] = compact('text', 'callback_data');

        $this->payload['reply_markup'] = json_encode([
            'inline_keyboard' => array_chunk($this->buttons, $columns),
        ]);

        return $this;
    }

    /**
     * Send the message silently.
     * Users will receive a notification with no sound.
     *
     * @return $this
     */
    public function disableNotification(bool $disableNotification = true): self
    {
        $this->payload['disable_notification'] = $disableNotification;

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
