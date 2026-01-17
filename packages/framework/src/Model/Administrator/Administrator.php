<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Override;
use Ramsey\Uuid\Uuid;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Security\ResetPasswordInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'administrators')]
#[ORM\Index(columns: ['username'])]
#[ORM\Entity]
class Administrator implements UserInterface, UniqueLoginInterface, TimelimitLoginInterface, PasswordAuthenticatedUserInterface, EmailTwoFactorInterface, GoogleTwoFactorInterface, ResetPasswordInterface
{
    public const string TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL = 'email';
    public const string TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH = 'google_auth';

    public const array TWO_FACTOR_AUTHENTICATION_TYPES = [
        self::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL,
        self::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH,
    ];

    protected const int RESET_PASSWORD_HASH_VALID_HOURS = 24;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    protected $username;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $realName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $password;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32)]
    protected $loginToken;

    /**
     * @var \DateTimeImmutable
     */
    protected $lastActivity;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $email;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit>
     */
    #[ORM\OneToMany(targetEntity: AdministratorGridLimit::class, mappedBy: 'administrator', cascade: ['persist'], orphanRemoval: true)]
    protected $gridLimits;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole>
     */
    #[ORM\OneToMany(targetEntity: AdministratorRole::class, mappedBy: 'administrator', cascade: ['persist'], orphanRemoval: true)]
    protected $roles;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $rolesChangedAt;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $transferIssuesLastSeenDateTime;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    protected $twoFactorAuthenticationType;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    protected $emailAuthenticationCode;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected $googleAuthenticatorSecret;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    #[ORM\JoinColumn(name: 'role_group_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: AdministratorRoleGroup::class)]
    protected $roleGroup;

    /**
     * @var int[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    protected $displayOnlyDomainIds;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10)]
    protected $selectedLocale;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $resetPasswordHash;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $resetPasswordHashValidThrough;

    public function __construct(AdministratorData $administratorData)
    {
        $this->lastActivity = new DatePoint();
        $this->gridLimits = new ArrayCollection();
        $this->loginToken = '';
        $this->roles = new ArrayCollection();
        $this->transferIssuesLastSeenDateTime = $administratorData->transferIssuesLastSeenDateTime;
        $this->uuid = Uuid::uuid4()->toString();
        $this->selectedLocale = $administratorData->selectedLocale;
        $this->setData($administratorData);
    }

    public function edit(AdministratorData $administratorData): void
    {
        $this->setData($administratorData);
    }

    protected function setData(AdministratorData $administratorData): void
    {
        $this->email = $administratorData->email;
        $this->realName = $administratorData->realName;
        $this->username = $administratorData->username;
        $this->roleGroup = $administratorData->roleGroup;
        $this->displayOnlyDomainIds = $administratorData->displayOnlyDomainIds;
    }

    public function getGridLimit(string $gridId): ?AdministratorGridLimit
    {
        foreach ($this->gridLimits as $gridLimit) {
            if ($gridLimit->getGridId() === $gridId) {
                return $gridLimit;
            }
        }

        return null;
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
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @return string
     */
    #[Override]
    public function getEmail()
    {
        return $this->email;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    #[Override]
    public function getLoginToken()
    {
        return $this->loginToken;
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
     * @return bool
     */
    public function isSuperadmin()
    {
        foreach ($this->roles as $role) {
            if ($role->getRole() === SystemRole::SUPER_ADMIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $realName
     */
    public function setRealname($realName)
    {
        $this->realName = $realName;
    }

    public function setPasswordHash(string $passwordHash)
    {
        $this->password = $passwordHash;
        $this->resetPasswordHash = null;
        $this->resetPasswordHashValidThrough = null;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole[]
     */
    public function getAdministratorRoles(): array
    {
        return $this->roles->getValues();
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getRolesChangedAt()
    {
        return $this->rolesChangedAt;
    }

    public function setRolesChangedNow(): void
    {
        $this->rolesChangedAt = new DatePoint();
    }

    /**
     * @param string $loginToken
     */
    #[Override]
    public function setLoginToken($loginToken)
    {
        $this->loginToken = $loginToken;
    }

    /**
     * @param \DateTimeImmutable $lastActivity
     */
    #[Override]
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @return array{id: int, username: string, password: string, realName: string, loginToken: string, timestamp: int, rolesChangedAt: ?\DateTimeImmutable}
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'realName' => $this->realName,
            'loginToken' => $this->loginToken,
            'timestamp' => time(),
            'rolesChangedAt' => $this->rolesChangedAt,
        ];
    }

    /**
     * @param array{id: int, username: string, password: string, realName: string, loginToken: string, timestamp: int, rolesChangedAt: ?\DateTimeImmutable} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->realName = $data['realName'];
        $this->loginToken = $data['loginToken'];
        $this->rolesChangedAt = $data['rolesChangedAt'];
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
        if ($this->roles->exists(fn ($key, AdministratorRole $role) => $role->getRole() === SystemRole::SUPER_ADMIN)) {
            return [SystemRole::SUPER_ADMIN];
        }

        if ($this->roleGroup !== null) {
            return $this->roleGroup->getRoles();
        }

        $roles = [SystemRole::ADMIN];

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole $role */
        foreach ($this->roles->getValues() as $role) {
            $roles[] = $role->getRole();
        }

        return array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null; // bcrypt include salt in password hash
    }

    public function restoreGridLimit(Grid $grid)
    {
        $gridLimit = $this->getGridLimit($grid->getId());

        if ($gridLimit !== null) {
            $grid->setDefaultLimit($gridLimit->getLimit());
        }
    }

    public function addGridLimit(AdministratorGridLimit $administratorGridLimit): void
    {
        $this->gridLimits->add($administratorGridLimit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole[] $administratorRoles
     */
    public function addRoles(array $administratorRoles): void
    {
        $this->setRolesChangedNow();

        foreach ($administratorRoles as $role) {
            $this->roles->add($role);
        }
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getTransferIssuesLastSeenDateTime()
    {
        return $this->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \DateTimeImmutable $transferIssuesLastSeenDateTime
     */
    public function setTransferIssuesLastSeenDateTime($transferIssuesLastSeenDateTime): void
    {
        $this->transferIssuesLastSeenDateTime = $transferIssuesLastSeenDateTime;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    #[Override]
    public function isEmailAuthEnabled(): bool
    {
        return $this->twoFactorAuthenticationType === self::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL;
    }

    #[Override]
    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }

    #[Override]
    public function getEmailAuthCode(): ?string
    {
        return $this->emailAuthenticationCode;
    }

    #[Override]
    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthenticationCode = $authCode;
    }

    #[Override]
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->twoFactorAuthenticationType === self::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH;
    }

    #[Override]
    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUsername();
    }

    #[Override]
    public function getGoogleAuthenticatorSecret(): string
    {
        if ($this->googleAuthenticatorSecret === null) {
            throw new LogicException(sprintf(
                "You should not call '%s' when 2FA by Google Authenticator is not enabled. Maybe it is a bug.",
                __METHOD__,
            ));
        }

        return $this->googleAuthenticatorSecret;
    }

    /**
     * @param string|null $googleAuthenticatorSecret
     */
    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function hasGeneratedGoogleAuthenticatorSecret(): bool
    {
        return $this->googleAuthenticatorSecret !== null;
    }

    public function isEnabledTwoFactorAuth(): bool
    {
        return in_array($this->twoFactorAuthenticationType, self::TWO_FACTOR_AUTHENTICATION_TYPES, true);
    }

    public function enableEmailAuth(): void
    {
        $this->twoFactorAuthenticationType = self::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL;
    }

    public function enableGoogleAuthenticator(): void
    {
        $this->twoFactorAuthenticationType = self::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH;
    }

    public function disableTwoFactorAuth(): void
    {
        $this->twoFactorAuthenticationType = null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public function getRoleGroup()
    {
        return $this->roleGroup;
    }

    /**
     * @return int[]
     */
    public function getDisplayOnlyDomainIds()
    {
        return array_map('intval', $this->displayOnlyDomainIds);
    }

    /**
     * @param string $selectedLocale
     */
    public function setSelectedLocale($selectedLocale)
    {
        $this->selectedLocale = $selectedLocale;
    }

    /**
     * @return string
     */
    public function getSelectedLocale()
    {
        return $this->selectedLocale;
    }

    /**
     * @param string $resetPasswordHash
     */
    public function setResetPasswordHash($resetPasswordHash): void
    {
        $this->resetPasswordHash = $resetPasswordHash;
        $this->resetPasswordHashValidThrough = (new DatePoint())->modify('+' . self::RESET_PASSWORD_HASH_VALID_HOURS . ' hours');
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    #[Override]
    public function isResetPasswordHashValid(?string $hash): bool
    {
        if ($hash === null || $this->resetPasswordHash !== $hash) {
            return false;
        }

        return $this->resetPasswordHashValidThrough !== null && $this->resetPasswordHashValidThrough >= new DatePoint();
    }
}
