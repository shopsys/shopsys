<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class VatFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(VatData $data, int $domainId): Vat
    {
        $entityClassName = $this->entityNameResolver->resolve(Vat::class);

        return new $entityClassName($data, $domainId);
    }
}
