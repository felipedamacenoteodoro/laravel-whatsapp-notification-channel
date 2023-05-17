<?php

namespace NotificationChannels\Whatsapp;

use Illuminate\Support\Facades\View;
use JsonSerializable;
use NotificationChannels\Whatsapp\Traits\HasSharedLogic;

/**
 * Class WhatsappMessage.
 */
class WhatsappMessage implements JsonSerializable
{
    use HasSharedLogic;

    /** @var int Message Chunk Size */
    public $chunkSize;

    public function __construct(string $content = '')
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'Markdown';
        $this->payload['isGroup'] = false;
    }

    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    /**
     * Notification message (Supports Markdown).
     *
     * @return $this
     */
    public function content(string $content, int $limit = null): self
    {
        $this->payload['text'] = $content;
        $this->payload['message'] = $content;

        if ($limit) {
            $this->chunkSize = $limit;
        }

        return $this;
    }

    /**
     * Attach a view file as the content for the notification.
     * Supports Laravel blade template.
     *
     * @return $this
     */
    public function view(string $view, array $data = [], array $mergeData = []): self
    {
        return $this->content(View::make($view, $data, $mergeData)->render());
    }

    /**
     * Chunk message to given size.
     *
     * @return $this
     */
    public function chunk(int $limit = 4096): self
    {
        $this->chunkSize = $limit;

        return $this;
    }

    /**
     * Should the message be chunked.
     */
    public function shouldChunk(): bool
    {
        return null !== $this->chunkSize;
    }
}
