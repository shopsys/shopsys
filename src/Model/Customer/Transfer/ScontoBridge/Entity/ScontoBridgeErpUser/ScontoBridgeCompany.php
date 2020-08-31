<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;

use JsonSerializable;

class ScontoBridgeCompany implements JsonSerializable
{
    private string $name;
    private string $companyNumber;
    private ?string $vatNumber;
    private ?string $taxNumber;

    public function __construct()
    {
        $this->vatNumber = null;
        $this->taxNumber = null;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $companyNumber
     */
    public function setCompanyNumber(string $companyNumber): void
    {
        $this->companyNumber = $companyNumber;
    }

    /**
     * @param string $vatNumber
     */
    public function setVatNumber(string $vatNumber): void
    {
        $this->vatNumber = $vatNumber;
    }

    /**
     * @param string $taxNumber
     */
    public function setTaxNumber(string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'companyNumber' => $this->companyNumber,
            'vatNumber' => $this->vatNumber,
            'taxNumber' => $this->taxNumber,
        ];
    }
}
