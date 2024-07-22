<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;

/**
 * @property \App\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @method \App\Model\Customer\User\CustomerUser getByUuid(string $uuid)
 * @method \App\Model\Customer\User\CustomerUser getCustomerUserById(int $customerUserId)
 * @method \App\Model\Customer\User\CustomerUser|null findCustomerUserByEmailAndDomain(string $email, int $domainId)
 * @method \App\Model\Customer\User\CustomerUser register(\App\Model\Customer\User\CustomerUserData $customerUserData)
 * @method \App\Model\Customer\User\CustomerUser create(\App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData)
 * @method \App\Model\Customer\User\CustomerUser createCustomerUser(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, \App\Model\Customer\User\CustomerUserData $customerUserData)
 * @method \App\Model\Customer\User\CustomerUser editByAdmin(int $customerUserId, \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData)
 * @method \App\Model\Customer\User\CustomerUser editByCustomerUser(int $customerUserId, \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData)
 * @method amendCustomerUserDataFromOrder(\App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Order\Order $order, \App\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method setEmail(string $email, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \Shopsys\FrameworkBundle\Model\Customer\Customer createCustomerWithBillingAddress(int $domainId, \App\Model\Customer\BillingAddressData $billingAddressData)
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
 * @property \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
 * @property \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
 * @property \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
 * @method updateCustomerUserByOrder(\App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Order\Order $order, string|null $deliveryAddressUuid, bool $isSubscribeToNewsletter)
 * @method \App\Model\Customer\DeliveryAddress|null resolveDeliveryAddress(string|null $deliveryAddressUuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\DeliveryAddress|null createDeliveryAddressForAmendingCustomerUserData(\App\Model\Order\Order $order)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\Mail\CustomerMailFacade $customerMailFacade, \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory, \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade, \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade, \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade, \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory, \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade, \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade, \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator)
 * @method \App\Model\Customer\User\CustomerUser edit(int $customerUserId, \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData, \App\Model\Customer\DeliveryAddress|null $deliveryAddress = null)
 * @method addRefreshTokenChain(\App\Model\Customer\User\CustomerUser $customerUser, string $refreshTokenChain, string $deviceId, \DateTime $tokenExpiration, \App\Model\Administrator\Administrator|null $administrator)
 * @method sendActivationMail(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method setDefaultDeliveryAddress(\App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Customer\DeliveryAddress $deliveryAddress)
 */
class CustomerUserFacade extends BaseCustomerUserFacade
{
}
