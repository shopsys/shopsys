<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
class User implements UserInterface, TimelimitLoginInterface, Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

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
     * @var DateTime
     */
    protected $lastActivity;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     * @ORM\OneToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\BillingAddress")
     * @ORM\JoinColumn(name="billing_address_id", referencedColumnName="id", nullable=false)
     */
    protected $billingAddress;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     * @ORM\OneToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $deliveryAddress;

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

    public function __construct(
        UserData $userData,
        BillingAddress $billingAddress,
        DeliveryAddress $deliveryAddress = null
    ) {
        $this->firstName = $userData->firstName;
        $this->lastName = $userData->lastName;
        $this->email = mb_strtolower($userData->email);
        $this->billingAddress = $billingAddress;
        $this->deliveryAddress = $deliveryAddress;
        if ($userData->createdAt !== null) {
            $this->createdAt = $userData->createdAt;
        } else {
            $this->createdAt = new \DateTime();
        }
        $this->domainId = $userData->domainId;
        $this->pricingGroup = $userData->pricingGroup;
    }

    public function edit(UserData $userData): void
    {
        $this->firstName = $userData->firstName;
        $this->lastName = $userData->lastName;
        $this->pricingGroup = $userData->pricingGroup;
    }
    
    public function changeEmail(string $email): void
    {
        $this->email = $email;
    }
    
    public function changePassword(string $password): void
    {
        $this->password = $password;
        $this->resetPasswordHash = null;
        $this->resetPasswordHashValidThrough = null;
    }
    
    public function setResetPasswordHash(string $hash): void
    {
        $this->resetPasswordHash = $hash;
        $this->resetPasswordHashValidThrough = new DateTime('+48 hours');
    }

    public function setDeliveryAddress(DeliveryAddress $deliveryAddress = null): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastActivity(): DateTime
    {
        return $this->lastActivity;
    }
    
    public function setLastActivity(DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    public function onLogin(): void
    {
        $this->lastLogin = new DateTime();
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }
    
    public function setDomainId(int $domainId): void
    {
        $this->domainId = $domainId;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFullName(): string
    {
        if ($this->billingAddress->isCompanyCustomer()) {
            return $this->billingAddress->getCompanyName();
        }

        return $this->lastName . ' ' . $this->firstName;
    }

    public function getBillingAddress(): \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
    {
        return $this->billingAddress;
    }

    public function getDeliveryAddress(): ?\Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
    {
        return $this->deliveryAddress;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function getPricingGroup(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->pricingGroup;
    }

    public function getResetPasswordHash(): ?string
    {
        return $this->resetPasswordHash;
    }

    public function getResetPasswordHashValidThrough(): ?\DateTime
    {
        return $this->resetPasswordHashValidThrough;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            // When unserialized user has empty password,
            // then UsernamePasswordToken is reloaded and ROLE_ADMIN_AS_CUSTOMER is lost.
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
        list(
            $this->id,
            $this->email,
            $this->password,
            $timestamp,
            $this->domainId
        ) = unserialize($serialized);
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
}
