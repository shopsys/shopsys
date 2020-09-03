<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;

use JsonSerializable;

class ScontoBridgeIndividual implements JsonSerializable
{
    /**
     * @var int
     */
    private int $individualTitle;

    /**
     * @var string
     */
    private string $firstName;

    /**
     * @var string
     */
    private string $lastName;

    /**
     * @var string|null
     */
    private ?string $birthDate;

    public function __construct()
    {
        $this->birthDate = null;
    }

    /**
     * @param int $individualTitle
     */
    public function setIndividualTitle(int $individualTitle): void
    {
        $this->individualTitle = $individualTitle;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $birthDate
     */
    public function setBirthDate(string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'individualTitle' => $this->individualTitle,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthDate' => $this->birthDate,
        ];
    }
}
