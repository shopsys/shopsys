<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUser as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserData as BaseUserData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;

/**
 * @ORM\Table(
 *     name="customer_users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="email_domain", columns={"email", "domain_id"})
 *     },
 *     indexes={
 *         @ORM\Index(columns={"email"})
 *     }
 * )
 * @ORM\Entity
 */
class CustomerUser extends BaseUser
{
    /**
     * @param \App\Model\Customer\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    public function __construct(
        BaseUserData $customerUserData,
        ?DeliveryAddress $deliveryAddress
    ) {
        parent::__construct($customerUserData, $deliveryAddress);
    }

    /**
     * @param \App\Model\Customer\CustomerUserData $customerUserData
     */
    public function edit(BaseUserData $customerUserData)
    {
        parent::edit($customerUserData);
    }
}
