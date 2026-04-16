<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable(false)]
#[ORM\Table(name: 'administrator_mcp_tokens')]
#[ORM\Entity]
class AdministratorMcpToken
{
    public const string MANUAL_CLIENT_ID = 'manual';
    public const string MANUAL_CLIENT_NAME = 'Manual token';
    public const int CLIENT_NAME_MAX_LENGTH = 255;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[ORM\JoinColumn(nullable: false, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    protected $administrator;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    protected $publicTokenId;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $secretHash;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32)]
    protected $clientId;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: self::CLIENT_NAME_MAX_LENGTH)]
    protected $clientName;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $lastUsedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $revokedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $replacedAt;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $expiresAt;

    public function __construct(AdministratorMcpTokenData $administratorMcpTokenData)
    {
        $this->administrator = $administratorMcpTokenData->administrator;
        $this->publicTokenId = $administratorMcpTokenData->publicTokenId;
        $this->secretHash = $administratorMcpTokenData->secretHash;
        $this->clientId = $administratorMcpTokenData->clientId;
        $this->clientName = $administratorMcpTokenData->clientName;
        $this->createdAt = $administratorMcpTokenData->createdAt;
        $this->lastUsedAt = $administratorMcpTokenData->lastUsedAt;
        $this->revokedAt = $administratorMcpTokenData->revokedAt;
        $this->replacedAt = $administratorMcpTokenData->replacedAt;
        $this->expiresAt = $administratorMcpTokenData->expiresAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @return string
     */
    public function getPublicTokenId()
    {
        return $this->publicTokenId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @return string
     */
    public function getSecretHash()
    {
        return $this->secretHash;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastUsedAt()
    {
        return $this->lastUsedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->revokedAt === null && $this->replacedAt === null;
    }

    public function isValidAt(DateTimeImmutable $dateTime): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->expiresAt > $dateTime;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function markUsed($dateTime): void
    {
        $this->lastUsedAt = $dateTime;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function revoke($dateTime): void
    {
        $this->revokedAt = $dateTime;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function replace($dateTime): void
    {
        $this->replacedAt = $dateTime;
    }
}
