<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OrganizationFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(int $domainId, OrganizationData $data): Organization
    {
        $entityClass = $this->entityNameResolver->resolve(Organization::class);

        return new $entityClass($domainId, $data);
    }
}
