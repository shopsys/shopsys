<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer;

use App\Model\Customer\DeliveryAddressDataFactory;
use App\Model\Customer\DeliveryAddressFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     */
    public function __construct(
        DeliveryAddressFacade $deliveryAddressFacade,
        CurrentCustomerUser $currentCustomerUser,
        DeliveryAddressDataFactory $deliveryAddressDataFactory
    ) {
        $this->deliveryAddressFacade = $deliveryAddressFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
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
            throw new UnauthorizedHttpException('You need to be logged in.');
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
            throw new UnauthorizedHttpException('You need to be logged in.');
        }

        $deliveryAddress = $this->deliveryAddressDataFactory
            ->createFromDeliveryInputArgumentAndCustomer($argument, $customerUser->getCustomer());

        $this->deliveryAddressFacade->editByCustomer($customerUser->getCustomer(), $deliveryAddress);

        /** @var \App\Model\Customer\DeliveryAddress[] $deliveryAddresses */
        $deliveryAddresses = $customerUser->getCustomer()->getDeliveryAddresses();

        return $deliveryAddresses;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'deleteDeliveryAddress' => 'deleteDeliveryAddress',
            'editDeliveryAddress' => 'editDeliveryAddress',
        ];
    }
}
