<?php

namespace NotificationChannels\Whatsapp;

use Illuminate\Support\Facades\View;
use JsonSerializable;
use NotificationChannels\Whatsapp\Traits\HasSharedLogic;
use Psr\Http\Message\StreamInterface;

/**
 * Class WhatsappFile.
 */
class WhatsappFile implements JsonSerializable
{
    use HasSharedLogic;

    /** @var string content document. */
    public $type = 'Document';

    public function __construct(string $content = '')
    {
        $this->content($content);
        $this->payload['parse_mode'] = 'Markdown';
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
    public function content(string $content): self
    {
        $this->payload['caption'] = $content;

        return $this;
    }

    /**
     * Add File to Message.
     *
     * Generic method to attach files of any type based on API.
     *
     * @param resource|StreamInterface|string|array $file
     *
     * @return $this
     */
    public function file($file, string $type, string $filename = null): self
    {
        $this->type = $type;

        if (null !== $filename) {
            $this->payload['fileName'] = $filename;
        }
        
        if (is_array($file) {
            $this->payload = array_merge($this->payload, $file);

            return $this;
        }

        if (is_string($file) && !$this->isReadableFile($file)) {
            $this->payload['path'] = $file;

            return $this;
        }

        $this->payload['file'] = [
            'name' => $type,
            'contents' => is_resource($file) ? $file : fopen($file, 'rb'),
        ];

        return $this;
    }

    /**
     * Attach an image.
     *
     * Use this method to send photos.
     *
     * @return $this
     */
    public function photo(string $file): self
    {
        return $this->file($file, 'photo');
    }

    /**
     * Attach an audio file.
     *
     * Use this method to send audio files, if you want Whatsapp clients to display them in the music player.
     * Your audio must be in the .mp3 format.
     *
     * @return $this
     */
    public function audio(string $file): self
    {
        return $this->file($file, 'audio');
    }

    /**
     * Attach a path or any file as path.
     *
     * Use this method to send general files.
     *
     * @return $this
     */
    public function document(string $file, string $filename = null): self
    {
        return $this->file($file, 'document', $filename);
    }

    /**
     * Attach a path or any file as path.
     *
     * Use this method to send general files.
     *
     * @return $this
     */
    public function file64(string $file, string $filename = null): self
    {
        return $this->file($file, 'file64', $filename);
    }

    /**
     * Attach a video file.
     *
     * Use this method to send video files, Whatsapp clients support mp4 videos.
     *
     * @return $this
     */
    public function video(string $file): self
    {
        return $this->file($file, 'video');
    }

    /**
     * Attach an animation file.
     *
     * Use this method to send animation files (GIF or H.264/MPEG-4 AVC video without sound).
     *
     * @return $this
     */
    public function animation(string $file): self
    {
        return $this->file($file, 'animation');
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
     * Determine there is a file.
     */
    public function hasFile(): bool
    {
        return is_array($this->payload['file'] ?? null) && is_resource($this->payload['file']['contents'] ?? null);
    }

    /**
     * Returns params payload.
     */
    public function toArray(): array
    {
        return $this->hasFile() ? $this->toMultipart() : $this->payload;
    }

    /**
     * Create Multipart array.
     */
    public function toMultipart(): array
    {
        $data = [];
        foreach ($this->payload as $name => $contents) {
            $data[] = ('file' === $name) ? $contents : compact('name', 'contents');
        }

        return $data;
    }

    /**
     * Determine if it's a regular and readable file.
     */
    protected function isReadableFile(string $file): bool
    {
        return is_file($file) && is_readable($file);
    }
}
