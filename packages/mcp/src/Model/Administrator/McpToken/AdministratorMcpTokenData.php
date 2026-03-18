<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public $administrator;

    /**
     * @var string|null
     */
    public $publicTokenId;

    /**
     * @var string|null
     */
    public $secretHash;

    /**
     * @var \DateTimeImmutable|null
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
}
