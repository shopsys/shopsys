<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation\Customer;

use App\Model\Customer\DeliveryAddressFacade;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
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
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        DeliveryAddressFacade $deliveryAddressFacade,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->deliveryAddressFacade = $deliveryAddressFacade;
        $this->currentCustomerUser = $currentCustomerUser;
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
            throw new UserError('You need to be logged in.');
        }

        $this->deliveryAddressFacade->deleteByUuidAndCustomer($deliveryAddressUuid, $customerUser->getCustomer());

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
        ];
    }
}
