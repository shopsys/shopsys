<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class VatFactory implements VatFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $data
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $data, int $domainId): Vat
    {
        $classData = $this->entityNameResolver->resolve(Vat::class);

        return new $classData($data, $domainId);
    }
}
