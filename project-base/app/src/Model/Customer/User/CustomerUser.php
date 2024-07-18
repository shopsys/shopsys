<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseUserData;

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
 * @property \App\Model\Customer\DeliveryAddress|null $defaultDeliveryAddress
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Customer\User\CustomerUserRefreshTokenChain> $refreshTokenChain
 * @method addRefreshTokenChain(\App\Model\Customer\User\CustomerUserRefreshTokenChain $customerUserRefreshTokenChain)
 * @method \App\Model\Customer\DeliveryAddress|null getDefaultDeliveryAddress()
 * @method setData(\App\Model\Customer\User\CustomerUserData $customerUserData)
 */
class CustomerUser extends BaseUser
{
    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function __construct(
        BaseUserData $customerUserData,
    ) {
        parent::__construct($customerUserData);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function edit(BaseUserData $customerUserData)
    {
        parent::edit($customerUserData);
    }
}
