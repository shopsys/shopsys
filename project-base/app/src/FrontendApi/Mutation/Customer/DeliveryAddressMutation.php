<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer;

use Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\DeliveryAddressMutation as BaseDeliveryAddressMutation;

/**
 * @property \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method __construct(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage, \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrontendApiBundle\Model\Mutation\Customer\DeliveryAddress\DeliveryAddressDataApiFactory $deliveryAddressDataApiFactory)
 * @method \App\Model\Customer\DeliveryAddress[] deleteDeliveryAddressMutation( $argument)
 * @method \App\Model\Customer\DeliveryAddress[] editDeliveryAddressMutation( $argument)
 * @method \App\Model\Customer\User\CustomerUser setDefaultDeliveryAddressMutation(string $deliveryAddressUuid)
 * @method \App\Model\Customer\DeliveryAddress[] createDeliveryAddressMutation( $argument)
 */
class DeliveryAddressMutation extends BaseDeliveryAddressMutation
{
}
