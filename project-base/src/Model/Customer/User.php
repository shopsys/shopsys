<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\UserData as BaseUserData;

/**
 * @ORM\Table(
 *     name="users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="email_domain", columns={"email", "domain_id"})
 *     },
 *     indexes={
 *         @ORM\Index(columns={"email"})
 *     }
 * )
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @param \App\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    public function __construct(
        BaseUserData $userData,
        ?DeliveryAddress $deliveryAddress
    ) {
        parent::__construct($userData, $deliveryAddress);
    }

    /**
     * @param \App\Model\Customer\UserData $userData
     */
    public function edit(BaseUserData $userData)
    {
        parent::edit($userData);
    }
}
