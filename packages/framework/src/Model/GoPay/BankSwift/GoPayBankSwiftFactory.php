<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GoPayBankSwiftFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift
     */
    public function create(GoPayBankSwiftData $goPayBankSwiftData): GoPayBankSwift
    {
        $className = $this->entityNameResolver->resolve(GoPayBankSwift::class);

        return new $className($goPayBankSwiftData);
    }
}
