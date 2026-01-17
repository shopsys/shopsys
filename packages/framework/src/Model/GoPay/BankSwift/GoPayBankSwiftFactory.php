<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GoPayBankSwiftFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(GoPayBankSwiftData $goPayBankSwiftData): GoPayBankSwift
    {
        $className = $this->entityNameResolver->resolve(GoPayBankSwift::class);

        return new $className($goPayBankSwiftData);
    }
}
