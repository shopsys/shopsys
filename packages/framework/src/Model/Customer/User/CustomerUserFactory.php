<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;

class CustomerUserFactory implements CustomerUserFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        CustomerUserPasswordFacade $customerUserPasswordFacade
    ) {
        $this->entityNameResolver = $entityNameResolver;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress): CustomerUser
    {
        $classData = $this->entityNameResolver->resolve(CustomerUser::class);

        $customerUser = new $classData($customerUserData, $deliveryAddress);

        $this->customerUserPasswordFacade->changePassword($customerUser, $customerUserData->password);

        return $customerUser;
    }
}
