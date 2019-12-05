<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class UserFactory implements UserFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade
     */
    protected $customerPasswordFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade $customerPasswordFacade
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        CustomerUserPasswordFacade $customerPasswordFacade
    ) {
        $this->entityNameResolver = $entityNameResolver;
        $this->customerPasswordFacade = $customerPasswordFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function create(
        UserData $userData,
        BillingAddress $billingAddress,
        ?DeliveryAddress $deliveryAddress
    ): User {
        $classData = $this->entityNameResolver->resolve(User::class);

        $user = new $classData($userData, $billingAddress, $deliveryAddress);

        $this->customerPasswordFacade->changePassword($user, $userData->password);

        return $user;
    }
}
