<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class InquiryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData $inquiryData
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry
     */
    public function create(InquiryData $inquiryData): Inquiry
    {
        $entityClassName = $this->entityNameResolver->resolve(Inquiry::class);

        return new $entityClassName($inquiryData);
    }
}
