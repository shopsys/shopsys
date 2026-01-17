<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class InquiryFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(InquiryData $inquiryData): Inquiry
    {
        $entityClassName = $this->entityNameResolver->resolve(Inquiry::class);

        return new $entityClassName($inquiryData);
    }
}
