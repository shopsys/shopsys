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
 */
class CustomerUser extends BaseUser
{
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $newsletterSubscription;

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

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    protected function setData(BaseUserData $customerUserData): void
    {
        parent::setData($customerUserData);

        $this->newsletterSubscription = $customerUserData->newsletterSubscription;
    }

    /**
     * @return bool
     */
    public function isNewsletterSubscription(): bool
    {
        return $this->newsletterSubscription;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $this->getCustomer()->getBillingAddress();

        return $billingAddress->isActivated();
    }
}
