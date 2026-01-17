<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserLoginTypeFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ): CustomerUserLoginType {
        $entityName = $this->entityNameResolver->resolve(CustomerUserLoginType::class);

        return new $entityName($customerUserLoginTypeData);
    }
}
