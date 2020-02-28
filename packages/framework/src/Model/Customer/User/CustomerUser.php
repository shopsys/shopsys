<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
class CustomerUser implements UserInterface, TimelimitLoginInterface, Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\Customer")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /**
     * @var \DateTime
     */
    protected $lastActivity;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup")
     * @ORM\JoinColumn(name="pricing_group_id", referencedColumnName="id", nullable=false)
     */
    protected $pricingGroup;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetPasswordHashValidThrough;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress")
     * @ORM\JoinColumn(name="default_delivery_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $defaultDeliveryAddress;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function __construct(CustomerUserData $customerUserData)
    {
        $this->firstName = $customerUserData->firstName;
        $this->lastName = $customerUserData->lastName;
        if ($customerUserData->createdAt !== null) {
            $this->createdAt = $customerUserData->createdAt;
        } else {
            $this->createdAt = new \DateTime();
        }
        $this->domainId = $customerUserData->domainId;
        $this->pricingGroup = $customerUserData->pricingGroup;
        $this->telephone = $customerUserData->telephone;
        $this->setEmail($customerUserData->email);
        $this->customer = $customerUserData->customer;
        $this->defaultDeliveryAddress = $customerUserData->defaultDeliveryAddress;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function edit(CustomerUserData $customerUserData)
    {
        $this->firstName = $customerUserData->firstName;
        $this->lastName = $customerUserData->lastName;
        $this->pricingGroup = $customerUserData->pricingGroup;
        $this->telephone = $customerUserData->telephone;
        $this->defaultDeliveryAddress = $customerUserData->defaultDeliveryAddress;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = mb_strtolower($email);
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash(string $passwordHash): void
    {
        $this->password = $passwordHash;
        $this->resetPasswordHash = null;
        $this->resetPasswordHashValidThrough = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    public function onLogin()
    {
        $this->lastLogin = new DateTime();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
            return $this->getCustomer()->getBillingAddress()->getCompanyName();
        }

        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return string|null
     */
    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            time(), // lastActivity
            $this->domainId,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->email,
            $this->password,
            $timestamp,
            $this->domainId
        ] = unserialize($serialized);
        $this->lastActivity = new DateTime();
        $this->lastActivity->setTimestamp($timestamp);
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return [Roles::ROLE_LOGGED_CUSTOMER];
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null; // bcrypt include salt in password hash
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param string $resetPasswordHash
     */
    public function setResetPasswordHash(string $resetPasswordHash): void
    {
        $this->resetPasswordHash = $resetPasswordHash;
        $this->resetPasswordHashValidThrough = new DateTime('+48 hours');
    }

    /**
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid(?string $hash): bool
    {
        if ($hash === null || $this->resetPasswordHash !== $hash) {
            return false;
        }

        $now = new DateTime();

        return $this->resetPasswordHashValidThrough !== null && $this->resetPasswordHashValidThrough >= $now;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function getDefaultDeliveryAddress(): ?DeliveryAddress
    {
        return $this->defaultDeliveryAddress;
    }
}
