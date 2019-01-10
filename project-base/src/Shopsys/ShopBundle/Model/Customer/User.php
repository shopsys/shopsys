<?php

namespace Shopsys\ShopBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\UserData as BaseUserData;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

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
     * @param \Shopsys\ShopBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @param \Shopsys\ShopBundle\Model\Customer\User|null $userByEmail
     */
    public function __construct(
        BaseUserData $userData,
        BillingAddress $billingAddress,
        ?DeliveryAddress $deliveryAddress,
        ?BaseUser $userByEmail
    ) {
        parent::__construct($userData, $billingAddress, $deliveryAddress, $userByEmail);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Customer\UserData $userData
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    public function edit(BaseUserData $userData, EncoderFactoryInterface $encoderFactory)
    {
        parent::edit($userData, $encoderFactory);
    }
}
