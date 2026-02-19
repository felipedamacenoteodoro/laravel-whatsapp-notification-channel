<?php

namespace NotificationChannels\Whatsapp;

use JsonSerializable;
use NotificationChannels\Whatsapp\Traits\HasSharedLogic;

/**
 * Class WhatsappContact.
 */
class WhatsappContact implements JsonSerializable
{
    use HasSharedLogic;

    public function __construct(string $phoneNumber = '')
    {
        $this->setNumberKey();
        $this->setMessageKey(
            in_array(Whatsapp::$apiServer, ['wppconnect-server', 'whatsapp-http-api']) ? 'contactsId' : 'contact'
        );
        $this->phoneNumber($phoneNumber);
    }

    public static function create(string $phoneNumber = ''): self
    {
        return new self($phoneNumber);
    }

    /**
     * Contact phone number.
     *
     * @return $this
     */
    public function phoneNumber(string $phoneNumber): self
    {
        $this->payload[$this->messageKey] = $phoneNumber;

        return $this;
    }

    /**
     * Contact first name.
     *
     * @return $this
     */
    public function firstName(string $firstName): self
    {
        $this->payload['name'] = $firstName;
        $this->payload['first_name'] = $firstName;

        return $this;
    }


    public function name(string $name): self
    {
        $this->payload['name'] = $name;

        return $this;
    }

    /**
     * Contact last name.
     *
     * @return $this
     */
    public function lastName(string $lastName): self
    {
        $this->payload['name'] .= ' ' . $lastName;
        $this->payload['last_name'] = $lastName;

        return $this;
    }

    /**
     * Contact vCard.
     *
     * @return $this
     */
    public function vCard(string $vCard): self
    {
        $this->payload['vcard'] = $vCard;

        return $this;
    }
}
