<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\Exception\DeliveryAddressNotFoundUserError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeliveryAddressMutation extends BaseTokenMutation
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\DeliveryAddressDataApiFactory $deliveryAddressDataApiFactory
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly DeliveryAddressDataApiFactory $deliveryAddressDataApiFactory,
    ) {
        parent::__construct($tokenStorage);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public function deleteDeliveryAddressMutation(Argument $argument): array
    {
        $user = $this->runCheckUserIsLogged();
        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());

        $deliveryAddressUuid = $argument['deliveryAddressUuid'];
        $this->deliveryAddressFacade->deleteByUuidAndCustomer($deliveryAddressUuid, $customerUser->getCustomer());

        return $customerUser->getCustomer()->getDeliveryAddresses();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public function editDeliveryAddressMutation(Argument $argument): array
    {
        $user = $this->runCheckUserIsLogged();
        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());

        $deliveryAddress = $this->deliveryAddressDataApiFactory
            ->createFromDeliveryInputArgumentAndCustomer($argument, $customerUser->getCustomer());

        $this->deliveryAddressFacade->editByCustomer($customerUser->getCustomer(), $deliveryAddress);

        return $customerUser->getCustomer()->getDeliveryAddresses();
    }

    /**
     * @param string $deliveryAddressUuid
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function setDefaultDeliveryAddressMutation(string $deliveryAddressUuid): CustomerUser
    {
        $user = $this->runCheckUserIsLogged();
        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());

        try {
            $deliveryAddress = $this->deliveryAddressFacade->getByUuidAndCustomer(
                $deliveryAddressUuid,
                $customerUser->getCustomer(),
            );
        } catch (DeliveryAddressNotFoundException $exception) {
            throw new DeliveryAddressNotFoundUserError($exception->getMessage());
        }

        $this->customerUserFacade->setDefaultDeliveryAddress($customerUser, $deliveryAddress);

        return $customerUser;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public function createDeliveryAddressMutation(Argument $argument): array
    {
        $user = $this->runCheckUserIsLogged();
        $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());

        $deliveryAddressData = $this->deliveryAddressDataApiFactory
            ->createFromDeliveryInputArgumentAndCustomer($argument, $customerUser->getCustomer());

        $deliveryAddressData->addressFilled = true;
        $this->deliveryAddressFacade->createIfAddressFilled($deliveryAddressData);

        return $customerUser->getCustomer()->getDeliveryAddresses();
    }
}
