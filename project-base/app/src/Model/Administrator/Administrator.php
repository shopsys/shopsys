<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use App\Model\Administrator\RoleGroup\AdministratorRoleGroup;
use App\Model\Security\Roles;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Ramsey\Uuid\Uuid;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="administrators",
 *   indexes={
 *     @ORM\Index(columns={"username"})
 *   }
 * )
 */
class Administrator extends BaseAdministrator implements EmailTwoFactorInterface, GoogleTwoFactorInterface
{
    public const TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL = 'email';
    public const TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH = 'google_auth';

    public const TWO_FACTOR_AUTHENTICATION_TYPES = [
        self::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL,
        self::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH,
    ];

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $transferIssuesLastSeenDateTime;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private ?string $twoFactorAuthenticationType;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private ?string $emailAuthenticationCode;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $googleAuthenticatorSecret;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Administrator\RoleGroup\AdministratorRoleGroup")
     * @ORM\JoinColumn(name="role_group_id", referencedColumnName="id", nullable=true)
     */
    private ?AdministratorRoleGroup $roleGroup;

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function __construct(BaseAdministratorData $administratorData)
    {
        parent::__construct($administratorData);

        $this->uuid = Uuid::uuid4()->toString();
        $this->transferIssuesLastSeenDateTime = $administratorData->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function edit(BaseAdministratorData $administratorData): void
    {
        parent::edit($administratorData);
    }

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    protected function setData(BaseAdministratorData $administratorData): void
    {
        parent::setData($administratorData);

        $this->roleGroup = $administratorData->roleGroup;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime
     */
    public function getTransferIssuesLastSeenDateTime(): DateTime
    {
        return $this->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \DateTime $transferIssuesLastSeenDateTime
     */
    public function setTransferIssuesLastSeenDateTime(DateTime $transferIssuesLastSeenDateTime): void
    {
        $this->transferIssuesLastSeenDateTime = $transferIssuesLastSeenDateTime;
    }

    /**
     * @return bool
     */
    public function isEmailAuthEnabled(): bool
    {
        return $this->twoFactorAuthenticationType === self::TWO_FACTOR_AUTHENTICATION_TYPE_EMAIL;
    }

    /**
     * @return string
     */
    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }

    /**
     * @return string
     */
    public function getEmailAuthCode(): string
    {
        if ($this->emailAuthenticationCode === null) {
            throw new LogicException(sprintf(
                "You should not call '%s' when 2FA by email is not enabled. Maybe it is a bug.",
                __METHOD__
            ));
        }

        return $this->emailAuthenticationCode;
    }

    /**
     * @param string $authCode
     */
    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthenticationCode = $authCode;
    }

    /**
     * @return bool
     */
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->twoFactorAuthenticationType === self::TWO_FACTOR_AUTHENTICATION_TYPE_GOOGLE_AUTH;
    }

    /**
     * @return string
     */
    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUsername();
    }

    /**
     * @return string
     */
    public function getGoogleAuthenticatorSecret(): string
    {
        if ($this->googleAuthenticatorSecret === null) {
            throw new LogicException(sprintf(
                "You should not call '%s' when 2FA by Google Authenticator is not enabled. Maybe it is a bug.",
                __METHOD__
            ));
        }
        return $this->googleAuthenticatorSecret;
    }

    /**
     * @param string $googleAuthenticatorSecret
     */
    public function setGoogleAuthenticatorSecret(string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    /**
     * @return bool
     */
    public function hasGeneratedGoogleAuthenticatorSecret(): bool
    {
        return $this->googleAuthenticatorSecret !== null;
    }

    /**
     * @return bool
     */
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
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public function getRoleGroup(): ?AdministratorRoleGroup
    {
        return $this->roleGroup;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        if ($this->roleGroup !== null) {
            $roles = $this->roleGroup->getRoles();
            return array_merge($roles, [Roles::ROLE_ADMIN]);
        }

        return parent::getRoles();
    }
}
