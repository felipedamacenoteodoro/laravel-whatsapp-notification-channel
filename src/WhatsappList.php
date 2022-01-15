<?php

namespace NotificationChannels\Whatsapp;

use JsonSerializable;
use NotificationChannels\Whatsapp\Traits\HasSharedLogic;

/**
 * Class WhatsappLis.
 */
class WhatsappLis implements JsonSerializable
{
    use HasSharedLogic;

    public function __construct(string $question = '')
    {
        $this->question($question);
    }

    public static function create(string $question = ''): self
    {
        return new self($question);
    }

    /**
     * Poll question.
     *
     * @return $this
     */
    public function question(string $question): self
    {
        $this->payload['question'] = $question;

        return $this;
    }

    /**
     * Poll choices.
     *
     * @return $this
     */
    public function choices(array $choices): self
    {
        $this->payload['options'] = json_encode($choices);

        return $this;
    }
}
