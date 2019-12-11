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
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserPasswordFacade
     */
    protected $userPasswordFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserPasswordFacade $userPasswordFacade
     */
    public function __construct(
        EntityNameResolver $entityNameResolver,
        UserPasswordFacade $userPasswordFacade
    ) {
        $this->entityNameResolver = $entityNameResolver;
        $this->userPasswordFacade = $userPasswordFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function create(UserData $userData, ?DeliveryAddress $deliveryAddress): User
    {
        $classData = $this->entityNameResolver->resolve(User::class);

        $user = new $classData($userData, $deliveryAddress);

        $this->userPasswordFacade->changePassword($user, $userData->password);

        return $user;
    }
}
