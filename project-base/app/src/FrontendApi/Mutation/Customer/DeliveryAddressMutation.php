<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer;

use Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\DeliveryAddressMutation as BaseDeliveryAddressMutation;

/**
 * @property \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method __construct(\App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade)
 * @method \App\Model\Customer\DeliveryAddress[] deleteDeliveryAddressMutation(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Customer\DeliveryAddress[] editDeliveryAddressMutation(\Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method \App\Model\Customer\User\CustomerUser setDefaultDeliveryAddressMutation(string $deliveryAddressUuid)
 * @method \App\Model\Customer\DeliveryAddress[] createDeliveryAddressMutation(\Overblog\GraphQLBundle\Definition\Argument $argument)
 */
class DeliveryAddressMutation extends BaseDeliveryAddressMutation
{
}
