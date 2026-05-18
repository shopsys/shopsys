<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PostDeploy;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OneTimePostDeployTaskRecordFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(string $name, DateTimeImmutable $executedAt): OneTimePostDeployTaskRecord
    {
        $entityClassName = $this->entityNameResolver->resolve(OneTimePostDeployTaskRecord::class);

        return new $entityClassName($name, $executedAt);
    }
}
