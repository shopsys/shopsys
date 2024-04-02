<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class TransferFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param string $identifier
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    public function create(string $identifier, string $name): Transfer
    {
        $entityName = $this->entityNameResolver->resolve(Transfer::class);

        return new $entityName($identifier, $name);
    }
}
