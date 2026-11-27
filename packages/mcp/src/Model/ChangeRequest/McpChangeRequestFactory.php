<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class McpChangeRequestFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(McpChangeRequestData $mcpChangeRequestData): McpChangeRequest
    {
        $entityClassName = $this->entityNameResolver->resolve(McpChangeRequest::class);

        return new $entityClassName($mcpChangeRequestData);
    }
}
