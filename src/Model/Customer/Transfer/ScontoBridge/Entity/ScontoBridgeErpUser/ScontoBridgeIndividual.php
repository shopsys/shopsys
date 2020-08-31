<?php
declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;

use JsonSerializable;

class ScontoBridgeIndividual implements JsonSerializable
{
    private string $individualTitle;
    private string $firstName;
    private string $lastName;
    private string $birthDate;

    /**
     * @param string $individualTitle
     */
    public function setIndividualTitle(string $individualTitle): void
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

    public function jsonSerialize()
    {
        return [
            'individualTitle' => $this->individualTitle,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthDate' => $this->birthDate,
        ];
    }
}