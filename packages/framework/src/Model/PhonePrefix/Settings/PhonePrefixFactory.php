<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix\Settings;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PhonePrefixFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(int $domainId, string $code, bool $isDefault = false): PhonePrefix
    {
        $entityClassName = $this->entityNameResolver->resolve(PhonePrefix::class);

        return new $entityClassName($domainId, $code, $isDefault);
    }
}
