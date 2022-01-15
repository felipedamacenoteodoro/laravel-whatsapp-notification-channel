<?php

namespace NotificationChannels\Whatsapp;

/**
 * Class WhatsappUpdates.
 */
class WhatsappUpdates
{
    /** @var array Params payload. */
    protected $payload = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     * Watsapp updates limit.
     *
     * @return $this
     */
    public function limit(int $limit = null): self
    {
        $this->payload['limit'] = $limit;

        return $this;
    }

    /**
     * Additional options.
     *
     * @return $this
     */
    public function options(array $options): self
    {
        $this->payload = array_merge($this->payload, $options);

        return $this;
    }

    public function latest(): self
    {
        $this->payload['offset'] = -1;

        return $this;
    }

    public function get(): array
    {
        $response = app(Whatsapp::class)->getUpdates($this->payload);

        return json_decode($response->getBody()->getContents(), true);
    }
}
