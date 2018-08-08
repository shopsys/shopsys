<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
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
class Administrator implements UserInterface, Serializable, UniqueLoginInterface, TimelimitLoginInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, unique = true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $realName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $loginToken;

    /**
     * @var \DateTime
     */
    protected $lastActivity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit[]
     * @ORM\OneToMany(
     *     targetEntity="Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit",
     *     mappedBy="administrator",
     *     orphanRemoval=true
     * )
     */
    protected $gridLimits;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $superadmin;

    /**
     * @var bool
     */
    protected $multidomainLogin;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @var string
     */
    protected $multidomainLoginToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $multidomainLoginTokenExpiration;

    public function __construct(AdministratorData $administratorData)
    {
        $this->email = $administratorData->email;
        $this->realName = $administratorData->realName;
        $this->username = $administratorData->username;
        $this->lastActivity = new DateTime();
        $this->gridLimits = new ArrayCollection();
        $this->loginToken = '';
        $this->superadmin = $administratorData->superadmin;
        $this->multidomainLogin = false;
        $this->multidomainLoginToken = '';
        $this->multidomainLoginTokenExpiration = new DateTime();
    }

    public function edit(AdministratorData $administratorData): void
    {
        $this->email = $administratorData->email;
        $this->realName = $administratorData->realName;
        $this->username = $administratorData->username;
    }

    public function addGridLimit(AdministratorGridLimit $gridLimit): void
    {
        if (!$this->gridLimits->contains($gridLimit)) {
            $this->gridLimits->add($gridLimit);
        }
    }

    public function removeGridLimit(AdministratorGridLimit $gridLimit): void
    {
        $this->gridLimits->removeElement($gridLimit);
    }

    public function getGridLimit(string $gridId): \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit
    {
        foreach ($this->gridLimits as $gridLimit) {
            if ($gridLimit->getGridId() === $gridId) {
                return $gridLimit;
            }
        }
        return null;
    }

    public function getLimitByGridId(string $gridId): ?int
    {
        $gridLimit = $this->getGridLimit($gridId);
        if ($gridLimit !== null) {
            return $gridLimit->getLimit();
        }
        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRealName(): string
    {
        return $this->realName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getLoginToken(): string
    {
        return $this->loginToken;
    }

    public function getLastActivity(): \DateTime
    {
        return $this->lastActivity;
    }

    public function isSuperadmin(): bool
    {
        return $this->superadmin;
    }

    public function setSuperadmin(bool $superadmin): void
    {
        $this->superadmin = $superadmin;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setRealname(string $realName): void
    {
        $this->realName = $realName;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setLoginToken(string $loginToken): void
    {
        $this->loginToken = $loginToken;
    }

    public function setLastActivity(\DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    public function setMultidomainLoginTokenWithExpiration(
        string $multidomainLoginToken,
        DateTime $multidomainLoginTokenExpiration
    ): void {
        $this->multidomainLoginToken = $multidomainLoginToken;
        $this->multidomainLoginTokenExpiration = $multidomainLoginTokenExpiration;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->realName,
            $this->loginToken,
            time(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->realName,
            $this->loginToken,
            $timestamp
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
        if ($this->superadmin) {
            return [Roles::ROLE_SUPER_ADMIN];
        }
        return [Roles::ROLE_ADMIN];
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null; // bcrypt include salt in password hash
    }

    /**
     * @inheritDoc
     */
    public function isMultidomainLogin()
    {
        return $this->multidomainLogin;
    }

    /**
     * @inheritDoc
     */
    public function setMultidomainLogin($multidomainLogin)
    {
        $this->multidomainLogin = $multidomainLogin;
    }
}
