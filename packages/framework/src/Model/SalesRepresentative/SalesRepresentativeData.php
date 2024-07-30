<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

class SalesRepresentativeData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $telephone;

    public function __construct()
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative $salesRepresentative
     */
    public function fillFromEntity(SalesRepresentative $salesRepresentative): void
    {
        $this->uuid = $salesRepresentative->getUuid();
        $this->firstName = $salesRepresentative->getFirstName();
        $this->lastName = $salesRepresentative->getLastName();
        $this->email = $salesRepresentative->getEmail();
        $this->telephone = $salesRepresentative->getTelephone();
    }
}
