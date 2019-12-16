<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserFactory implements CustomerUserFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade $customerUserPasswordFacade
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        CustomerUserPasswordFacade $customerUserPasswordFacade
    ) {
        $this->entityNameResolver = $entityNameResolver;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUser
     */
    public function create(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress): CustomerUser
    {
        $classData = $this->entityNameResolver->resolve(CustomerUser::class);

        $customerUser = new $classData($customerUserData, $deliveryAddress);

        $this->customerUserPasswordFacade->changePassword($customerUser, $customerUserData->password);

        return $customerUser;
    }
}
