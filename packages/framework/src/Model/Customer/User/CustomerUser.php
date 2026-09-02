<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsMcpTable]
#[ORM\Table(name: 'customer_users')]
#[ORM\Index(columns: ['email'])]
#[ORM\UniqueConstraint(name: 'email_domain', columns: ['email', 'domain_id'])]
#[ORM\Entity]
class CustomerUser implements UserInterface, TimelimitLoginInterface, PasswordAuthenticatedUserInterface, ResetPasswordInterface, DomainSeparatedEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    protected $customer;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $firstName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $lastName;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $email {
        set {
            $this->email = mb_strtolower($value);
        }
    }

    /**
     * @var string|null
     */
    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $password;

    /**
     * @var \DateTimeImmutable
     */
    protected $lastActivity;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'pricing_group_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: PricingGroup::class)]
    protected $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'sales_representative_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: SalesRepresentative::class)]
    protected $salesRepresentative;

    /**
     * @var string|null
     */
    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $resetPasswordHash;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn(exposed: false)]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $resetPasswordHashValidThrough;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $telephonePrefix;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $telephonePrefixCountryCode;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $telephoneNumber;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'default_delivery_address_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: DeliveryAddress::class)]
    protected $defaultDeliveryAddress;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain>
     */
    #[ORM\OneToMany(targetEntity: CustomerUserRefreshTokenChain::class, mappedBy: 'customerUser', cascade: ['persist'])]
    protected $refreshTokenChain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'role_group_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: CustomerUserRoleGroup::class)]
    protected $roleGroup;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $lastSecurityChange;

    public function __construct(CustomerUserData $customerUserData)
    {
        $this->domainId = $customerUserData->domainId;
        $this->setEmail($customerUserData->email);
        $this->customer = $customerUserData->customer;
        $this->uuid = $customerUserData->uuid;
        $this->refreshTokenChain = new ArrayCollection();

        $this->createdAt = $customerUserData->createdAt;
        $this->setData($customerUserData);
    }

    public function edit(CustomerUserData $customerUserData): void
    {
        $this->setData($customerUserData);
    }

    protected function setData(CustomerUserData $customerUserData): void
    {
        $this->firstName = $customerUserData->firstName;
        $this->lastName = $customerUserData->lastName;
        $this->pricingGroup = $customerUserData->pricingGroup;
        $this->salesRepresentative = $customerUserData->salesRepresentative;
        $this->setTelephoneData($customerUserData->telephone);
        $this->defaultDeliveryAddress = $customerUserData->defaultDeliveryAddress;
        $this->roleGroup = $customerUserData->roleGroup;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->password = $passwordHash;
        $this->resetPasswordHash = null;
        $this->resetPasswordHashValidThrough = null;
    }

    /**
     * @return int
     */
    #[Override]
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return \DateTimeImmutable
     */
    #[Override]
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTimeImmutable $lastActivity
     */
    #[Override]
    public function setLastActivity($lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId): void
    {
        $this->domainId = $domainId;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    #[Override]
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

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
            return (string)$this->getCustomer()->getBillingAddress()->getCompanyName();
        }

        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return string
     */
    public function getCustomerUserFullName()
    {
        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative|null
     */
    public function getSalesRepresentative()
    {
        return $this->salesRepresentative;
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    /**
     * @return array{id: int , email: string, password: string, timestamp: int, domainId: int}
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->getPassword(),
            'timestamp' => time(), // lastActivity
            'domainId' => $this->domainId,
        ];
    }

    /**
     * @param array{id: int , email: string, password: string, timestamp: int, domainId: int} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->domainId = $data['domainId'];
        $this->lastActivity = (new DatePoint())->setTimestamp($data['timestamp']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function eraseCredentials(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRoles(): array
    {
        return $this->roleGroup->getRoles();
    }

    public function getTelephone(): ?string
    {
        return $this->getTelephoneData()?->toPhoneNumber();
    }

    public function getTelephoneData(): ?PhoneData
    {
        if ($this->telephoneNumber === null) {
            return null;
        }

        return new PhoneData($this->telephonePrefixCountryCode, $this->telephonePrefix, $this->telephoneNumber);
    }

    public function setTelephoneData(?PhoneData $phoneData): void
    {
        if ($phoneData === null) {
            $this->telephonePrefix = null;
            $this->telephonePrefixCountryCode = null;
            $this->telephoneNumber = null;

            return;
        }

        $this->telephonePrefix = $phoneData->prefix;
        $this->telephonePrefixCountryCode = $phoneData->countryCode;
        $this->telephoneNumber = $phoneData->number;
    }

    /**
     * @param string $resetPasswordHash
     */
    public function setResetPasswordHash($resetPasswordHash): void
    {
        $this->resetPasswordHash = $resetPasswordHash;
        $this->resetPasswordHashValidThrough = (new DatePoint())->modify('+48 hours');
    }

    #[Override]
    public function isResetPasswordHashValid(?string $hash): bool
    {
        if ($hash === null || $this->resetPasswordHash === null || !hash_equals($this->resetPasswordHash, $hash)) {
            return false;
        }

        return $this->resetPasswordHashValidThrough !== null && $this->resetPasswordHashValidThrough >= new DatePoint();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function getDefaultDeliveryAddress()
    {
        return $this->defaultDeliveryAddress;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function addRefreshTokenChain(CustomerUserRefreshTokenChain $customerUserRefreshTokenChain): void
    {
        $this->refreshTokenChain->add($customerUserRefreshTokenChain);
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return $this->getCustomer()->getBillingAddress()->isActivated();
    }

    public function hasPasswordSet(): bool
    {
        return $this->password !== null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $defaultDeliveryAddress
     */
    public function setDefaultDeliveryAddress($defaultDeliveryAddress): void
    {
        $this->defaultDeliveryAddress = $defaultDeliveryAddress;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getRoleGroup()
    {
        return $this->roleGroup;
    }

    public function updateLastSecurityChange(): void
    {
        $this->lastSecurityChange = new DatePoint();
    }
}
