<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;

class CreateOrderMutation extends AbstractMutation
{
    public const string VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED = 'isDeliveryAddressDifferentFromBillingWithoutPreselected';
    public const string VALIDATION_GROUP_ON_COMPANY_BEHALF = 'onCompanyBehalf';

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     */
    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly CreateOrderResultFactory $createOrderResultFactory,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly Domain $domain,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly PlaceOrderFacade $placeOrderFacade,
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly PlacedOrderMessageDispatcher $placedOrderMessageDispatcher,
        protected readonly DeliveryAddressFactory $deliveryAddressFactory,
        protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult
     */
    public function createOrderMutation(Argument $argument, InputValidator $validator): CreateOrderResult
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);

        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            return $this->createOrderResultFactory->getCreateOrderResultByCartWithModifications(
                $cartWithModifications,
            );
        }

        $orderInput = $this->orderInputFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        $order = $this->placeOrderFacade->placeOrder($orderData);

        if ($customerUser instanceof CustomerUser) {
            /** @var string|null $deliveryAddressUuid */
            $deliveryAddressUuid = $input['deliveryAddressUuid'];
            $deliveryAddress = $this->resolveDeliveryAddress($deliveryAddressUuid, $customerUser);
            $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
            $customerUserUpdateData->customerUserData->newsletterSubscription = $orderData->newsletterSubscription;
            $this->customerUserFacade->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);
            $deliveryAddress = $deliveryAddress ?? $this->createDeliveryAddressForAmendingCustomerUserData($order);
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
        } elseif ($orderData->newsletterSubscription) {
            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $orderData->email,
                $this->domain->getId(),
            );

            if ($newsletterSubscriber === null) {
                $this->newsletterFacade->addSubscribedEmail($orderData->email, $this->domain->getId());
            }
        }

        $this->placedOrderMessageDispatcher->dispatchPlacedOrderMessage($order->getId());

        $this->cartApiFacade->deleteCart($cart);
        $this->sendEmail($order);

        return $this->createOrderResultFactory->getCreateOrderResultByOrder($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function sendEmail(Order $order): void
    {
        $this->orderMailFacade->sendEmail($order);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_ON_COMPANY_BEHALF] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_ON_COMPANY_BEHALF;
        }

        if ($input['isDeliveryAddressDifferentFromBilling'] === true && $input['deliveryAddressUuid'] === null) {
            $validationGroups[] = self::VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED;
        }

        return $validationGroups;
    }

    /**
     * @param string|null $deliveryAddressUuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    protected function resolveDeliveryAddress(
        ?string $deliveryAddressUuid,
        ?CustomerUser $customerUser,
    ): ?DeliveryAddress {
        if ($deliveryAddressUuid === null || $customerUser === null) {
            return null;
        }

        return $this->deliveryAddressFacade->findByUuidAndCustomer(
            $deliveryAddressUuid,
            $customerUser->getCustomer(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    protected function createDeliveryAddressForAmendingCustomerUserData(Order $order): ?DeliveryAddress
    {
        $orderTransport = $order->getTransportItem()->getTransport();

        if (
            $orderTransport->isPersonalPickup() ||
            $orderTransport->isPacketery() ||
            $order->isDeliveryAddressSameAsBillingAddress()
        ) {
            return null;
        }

        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
        $deliveryAddressData->firstName = $order->getDeliveryFirstName();
        $deliveryAddressData->lastName = $order->getDeliveryLastName();
        $deliveryAddressData->companyName = $order->getDeliveryCompanyName();
        $deliveryAddressData->street = $order->getDeliveryStreet();
        $deliveryAddressData->city = $order->getDeliveryCity();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->country = $order->getDeliveryCountry();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->customer = $order->getCustomerUser()?->getCustomer();

        return $this->deliveryAddressFactory->create($deliveryAddressData);
    }
}
