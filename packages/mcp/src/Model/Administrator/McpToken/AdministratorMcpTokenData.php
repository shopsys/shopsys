<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public $administrator;

    /**
     * @var string
     */
    public $publicTokenId;

    /**
     * @var string
     */
    public $secretHash;

    /**
     * @var string
     */
    public $clientId;

    /**
     * @var string
     */
    public $clientName;

    /**
     * @var \DateTimeImmutable
     */
    public $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $lastUsedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $revokedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    public $replacedAt;

    /**
     * @var \DateTimeImmutable
     */
    public $expiresAt;
}
