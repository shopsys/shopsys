<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\MandatoryAdministratorRoleIsMissingException;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="administrators",
 *   indexes={
 *     @ORM\Index(columns={"username"})
 *   }
 * )
 */
class Administrator implements UserInterface, UniqueLoginInterface, TimelimitLoginInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique = true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $realName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $loginToken;

    /**
     * @var \DateTime
     */
    protected $lastActivity;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit>
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit",
     *     mappedBy="administrator",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    protected $gridLimits;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole>
     * @ORM\OneToMany(
     *     targetEntity="\Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole",
     *     mappedBy="administrator",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    protected $roles;

    /**
     * @var bool
     */
    protected $multidomainLogin = false;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $multidomainLoginToken;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $multidomainLoginTokenExpiration;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $rolesChangedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $transferIssuesLastSeenDateTime;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     */
    public function __construct(AdministratorData $administratorData)
    {
        $this->lastActivity = new DateTime();
        $this->gridLimits = new ArrayCollection();
        $this->loginToken = '';
        $this->multidomainLoginToken = '';
        $this->multidomainLoginTokenExpiration = new DateTime();
        $this->roles = new ArrayCollection();
        $this->transferIssuesLastSeenDateTime = $administratorData->transferIssuesLastSeenDateTime;
        $this->setData($administratorData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     */
    public function edit(AdministratorData $administratorData): void
    {
        $this->setData($administratorData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     */
    protected function setData(AdministratorData $administratorData): void
    {
        $this->email = $administratorData->email;
        $this->realName = $administratorData->realName;
        $this->username = $administratorData->username;
    }

    /**
     * @param string $gridId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit|null
     */
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

    /**
     * @return string
     */
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * @return \DateTime
     */
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
            if ($role->getRole() === Roles::ROLE_SUPER_ADMIN) {
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

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash(string $passwordHash)
    {
        $this->password = $passwordHash;
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
     * @return \DateTime|null
     */
    public function getRolesChangedAt()
    {
        return $this->rolesChangedAt;
    }

    public function setRolesChangedNow(): void
    {
        $this->rolesChangedAt = new DateTime();
    }

    /**
     * @param string $loginToken
     */
    public function setLoginToken($loginToken)
    {
        $this->loginToken = $loginToken;
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @param string $multidomainLoginToken
     * @param \DateTime $multidomainLoginTokenExpiration
     */
    public function setMultidomainLoginTokenWithExpiration(
        $multidomainLoginToken,
        DateTime $multidomainLoginTokenExpiration,
    ) {
        $this->multidomainLoginToken = $multidomainLoginToken;
        $this->multidomainLoginTokenExpiration = $multidomainLoginTokenExpiration;
    }

    /**
     * @return array{id: int, username: string, password: string, realName: string, loginToken: string, timestamp: int, rolesChangedAt: ?\DateTime}
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
     * @param array{id: int, username: string, password: string, realName: string, loginToken: string, timestamp: int, rolesChangedAt: ?\DateTime} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->realName = $data['realName'];
        $this->loginToken = $data['loginToken'];
        $this->rolesChangedAt = $data['rolesChangedAt'];
        $this->lastActivity = new DateTime();
        $this->lastActivity->setTimestamp($data['timestamp']);
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = [];
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole $role */
        foreach ($this->roles->getValues() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null; // bcrypt include salt in password hash
    }

    /**
     * {@inheritdoc}
     */
    public function isMultidomainLogin()
    {
        return $this->multidomainLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultidomainLogin($multidomainLogin)
    {
        $this->multidomainLogin = $multidomainLogin;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid $grid
     */
    public function restoreGridLimit(Grid $grid)
    {
        $gridLimit = $this->getGridLimit($grid->getId());

        if ($gridLimit !== null) {
            $grid->setDefaultLimit($gridLimit->getLimit());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit $administratorGridLimit
     */
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

        $this->checkRolesContainAdminRole();
    }

    protected function checkRolesContainAdminRole(): void
    {
        foreach ($this->roles->getValues() as $role) {
            if (in_array($role->getRole(), Roles::getMandatoryAdministratorRoles(), true)) {
                return;
            }
        }

        $message = sprintf(
            'There is no mandatory role for administrator with ID `%s`. One of this role is expected: %s.',
            $this->id,
            implode(', ', Roles::getMandatoryAdministratorRoles()),
        );

        throw new MandatoryAdministratorRoleIsMissingException($message);
    }

    /**
     * @return \DateTime
     */
    public function getTransferIssuesLastSeenDateTime()
    {
        return $this->transferIssuesLastSeenDateTime;
    }

    /**
     * @param \DateTime $transferIssuesLastSeenDateTime
     */
    public function setTransferIssuesLastSeenDateTime($transferIssuesLastSeenDateTime): void
    {
        $this->transferIssuesLastSeenDateTime = $transferIssuesLastSeenDateTime;
    }
}
