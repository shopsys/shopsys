<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class VatFactory implements VatFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $data
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     * @deprecated Will be removed in the next major release, use VatFactory::createForDomain instead
     */
    public function create(VatData $data, int $domainId): Vat
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the VatFactory::createForDomain instead.', __METHOD__), E_USER_DEPRECATED);

        $classData = $this->entityNameResolver->resolve(Vat::class);

        return new $classData($data, $domainId);
    }
}
