<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;

use JsonSerializable;

class ScontoBridgePrimaryAddress implements JsonSerializable
{
    private string $street;
    private string $city;
    private string $country;
    private string $zipCode;
    private int $id;

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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function jsonSerialize(): array
    {
        return [
            "street" => $this->street,
            "city" => $this->city,
            "country" => $this->country,
            "zipCode" => $this->zipCode,
            "id" => $this->id,
        ];
    }
}
