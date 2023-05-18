<?php

namespace NotificationChannels\Whatsapp;

use JsonSerializable;
use NotificationChannels\Whatsapp\Traits\HasSharedLogic;

/**
 * Class WhatsappLocation.
 */
class WhatsappLocation implements JsonSerializable
{
    use HasSharedLogic;

    /** @var string */
    private $latKey;

    /** @var string */
    private $lngKey;

    /**
     * whatsapp Location constructor.
     *
     * @param null|float|string $latitude
     * @param null|float|string $longitude
     */
    public function __construct($latitude = null, $longitude = null)
    {
        $this->setNumberKey();
        $this->setMessageKey(Whatsapp::$apiServer == 'wppconnect-server' ? 'address' : 'description');
        $this->latKey = Whatsapp::$apiServer == 'whatsapp-http-api' ? 'latitude' : 'lat';
        $this->latitude($latitude);
        $this->lngKey = Whatsapp::$apiServer == 'whatsapp-http-api' ? 'longitude' : (Whatsapp::$apiServer == 'wppconnect-server' ? 'lng' : 'log');
        $this->longitude($longitude);
    }

    /**
     * @param null|float|string $latitude
     * @param null|float|string $longitude
     *
     * @return static
     */
    public static function create($latitude = null, $longitude = null): self
    {
        return new static($latitude, $longitude);
    }

    /**
     * Location's latitude.
     *
     * @param float|string $latitude
     *
     * @return $this
     */
    public function latitude($latitude): self
    {
        $this->payload[$this->latKey] = $latitude;

        return $this;
    }

     /**
     * Location's longitude.
     *
     * @param float|string $longitude
     *
     * @return $this
     */
    public function longitude($longitude): self
    {
        $this->payload[$this->lngKey] = $longitude;

        return $this;
    }

    /**
     * Location's title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title($title): self
    {
        $this->payload['title'] = $title;

        return $this;
    }

    /**
     * Location's description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description($description): self
    {
        $this->payload[$this->messageKey] = $description;

        return $this;
    }
}
