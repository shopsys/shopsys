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
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class DeliveryAddressMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\Model\Customer\DeliveryAddressFacade
     */
    private DeliveryAddressFacade $deliveryAddressFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Customer\DeliveryAddressDataFactory
     */
    private DeliveryAddressDataFactory $deliveryAddressDataFactory;

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory;

    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        DeliveryAddressFacade $deliveryAddressFacade,
        CurrentCustomerUser $currentCustomerUser,
        DeliveryAddressDataFactory $deliveryAddressDataFactory,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->deliveryAddressFacade = $deliveryAddressFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\Model\Customer\DeliveryAddress[]
     */
    public function deleteDeliveryAddress(Argument $argument, InputValidator $validator): array
    {
        $validator->validate();

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
    public function editDeliveryAddress(Argument $argument): array
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
    public function setDefaultDeliveryAddress(string $deliveryAddressUuid): CustomerUser
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'deleteDeliveryAddress' => 'deleteDeliveryAddress',
            'editDeliveryAddress' => 'editDeliveryAddress',
            'setDefaultDeliveryAddress' => 'setDefaultDeliveryAddress',
        ];
    }
}
