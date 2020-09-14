<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;

use JsonSerializable;

class ScontoBridgePrimaryAddress implements JsonSerializable
{
    /**
     * @var string
     */
    private string $street;

    /**
     * @var string
     */
    private string $city;

    /**
     * @var string
     */
    private string $country;

    /**
     * @var string
     */
    private string $zipCode;

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "street" => $this->street,
            "city" => $this->city,
            "country" => $this->country,
            "zipCode" => $this->zipCode,
        ];
    }
}
