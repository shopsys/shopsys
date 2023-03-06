<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer;

use App\FrontendApi\Mutation\Customer\Exception\DeliveryAddressNotFoundUserError;
use App\FrontendApi\Mutation\Login\Exception\InvalidCredentialsUserError;
use App\Model\Customer\DeliveryAddressDataFactory;
use App\Model\Customer\DeliveryAddressFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class DeliveryAddressMutation extends AbstractMutation
{
    /**
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        private readonly DeliveryAddressFacade $deliveryAddressFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        private readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        private readonly CustomerUserFacade $customerUserFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Customer\DeliveryAddress[]
     */
    public function deleteDeliveryAddressMutation(Argument $argument): array
    {
        $deliveryAddressUuid = $argument['deliveryAddressUuid'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null) {
            throw new InvalidCredentialsUserError('You need to be logged in.');
        }

        $this->deliveryAddressFacade->deleteByUuidAndCustomer($deliveryAddressUuid, $customerUser->getCustomer());

        /** @var \App\Model\Customer\DeliveryAddress[] $deliveryAddresses */
        $deliveryAddresses = $customerUser->getCustomer()->getDeliveryAddresses();

        return $deliveryAddresses;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Customer\DeliveryAddress[]
     */
    public function editDeliveryAddressMutation(Argument $argument): array
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null) {
            throw new InvalidCredentialsUserError('You need to be logged in.');
        }

        $deliveryAddress = $this->deliveryAddressDataFactory
            ->createFromDeliveryInputArgumentAndCustomer($argument, $customerUser->getCustomer());

        $this->deliveryAddressFacade->editByCustomer($customerUser->getCustomer(), $deliveryAddress);

        /** @var \App\Model\Customer\DeliveryAddress[] $deliveryAddresses */
        $deliveryAddresses = $customerUser->getCustomer()->getDeliveryAddresses();

        return $deliveryAddresses;
    }

    /**
     * @param string $deliveryAddressUuid
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function setDefaultDeliveryAddressMutation(string $deliveryAddressUuid): CustomerUser
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null) {
            throw new InvalidCredentialsUserError('You need to be logged in.');
        }

        try {
            $deliveryAddress = $this->deliveryAddressFacade->getByUuidAndCustomer(
                $deliveryAddressUuid,
                $customerUser->getCustomer()
            );
        } catch (DeliveryAddressNotFoundException $exception) {
            throw new DeliveryAddressNotFoundUserError($exception->getMessage());
        }

        $customerData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $this->customerUserFacade->edit($customerUser->getId(), $customerData, $deliveryAddress);

        return $customerUser;
    }
}
