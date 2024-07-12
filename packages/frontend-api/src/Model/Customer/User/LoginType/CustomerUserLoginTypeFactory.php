<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserLoginTypeFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData $customerUserLoginTypeData
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType
     */
    public function create(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ): CustomerUserLoginType {
        $entityName = $this->entityNameResolver->resolve(CustomerUserLoginType::class);

        return new $entityName($customerUserLoginTypeData);
    }
}
