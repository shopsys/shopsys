<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorMcpTokenFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        AdministratorMcpTokenData $administratorMcpTokenData,
        string $secretHash,
    ): AdministratorMcpToken {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorMcpToken::class);

        return new $entityClassName($administratorMcpTokenData, $secretHash);
    }
}
