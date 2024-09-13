<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserFactory implements CustomerUserFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserData $customerUserData): CustomerUser
    {
        $entityClassName = $this->entityNameResolver->resolve(CustomerUser::class);

        $customerUser = new $entityClassName($customerUserData);

        if ($customerUserData->password !== null) {
            $this->customerUserPasswordFacade->setPassword($customerUser, $customerUserData->password);
        }

        return $customerUser;
    }
}
