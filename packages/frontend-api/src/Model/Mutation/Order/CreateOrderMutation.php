<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;

class CreateOrderMutation extends AbstractMutation
{
    public const string VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED = 'isDeliveryAddressDifferentFromBillingWithoutPreselected';
    public const string VALIDATION_GROUP_ON_COMPANY_BEHALF = 'onCompanyBehalf';
    public const string VALIDATION_GROUP_ANONYMOUS_USER = 'anonymousUser';

    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CreateOrderResultFactory $createOrderResultFactory,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly Domain $domain,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly PlaceOrderFacade $placeOrderFacade,
        protected readonly OrderInputFactory $orderInputFactory,
    ) {
    }

    public function createOrderMutation(Argument $argument, InputValidator $validator): CreateOrderResult
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $input = $argument['input'];
        $deliveryAddressUuid = $input['deliveryAddressUuid'];

        $this->validateCreateOrderMutation($argument, $validator, $customerUser);

        $cart = $this->getCartForCreateOrderMutation($input, $customerUser);

        $cartWithModifications = $this->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            return $this->createOrderResultFactory->getCreateOrderResultByCartWithModifications(
                $cartWithModifications,
            );
        }

        $order = $this->createOrderFromCart($argument, $cart, $deliveryAddressUuid);

        $this->cartApiFacade->deleteCart($cart);

        return $this->createOrderResultFactory->getCreateOrderResultByOrder($order);
    }

    protected function validateCreateOrderMutation(
        Argument $argument,
        InputValidator $validator,
        ?CustomerUser $customerUser,
    ): void {
        $validationGroups = $this->computeValidationGroups($argument, $customerUser);
        $validator->validate($validationGroups);
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function getCartForCreateOrderMutation(array $input, ?CustomerUser $customerUser): Cart
    {
        /** @var string|null $cartUuid */
        $cartUuid = $input['cartUuid'];

        return $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
    }

    protected function getCheckedCartWithModifications(Cart $cart): CartWithModificationsResult
    {
        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    protected function createOrderFromCart(
        Argument $argument,
        Cart $cart,
        ?string $deliveryAddressUuid,
    ): Order {
        $processedOrderData = $this->getProcessedOrderData($argument, $cart, $deliveryAddressUuid);

        return $this->placeOrderFacade->placeOrder($processedOrderData, $deliveryAddressUuid);
    }

    protected function getProcessedOrderData(
        Argument $argument,
        Cart $cart,
        ?string $deliveryAddressUuid,
    ): OrderData {
        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);
        $orderInput = $this->createOrderInputFromCart($cart, $deliveryAddressUuid);

        return $this->orderProcessor->process($orderInput, $orderData);
    }

    protected function createOrderInputFromCart(Cart $cart, ?string $deliveryAddressUuid): OrderInput
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());
        $orderInput->addAdditionalData(
            SetDeliveryAddressByDeliveryAddressUuidMiddleware::DELIVERY_ADDRESS_UUID,
            $deliveryAddressUuid,
        );

        return $orderInput;
    }

    /**
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument, ?CustomerUser $currentCustomerUser): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_ON_COMPANY_BEHALF] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_ON_COMPANY_BEHALF;
        }

        if ($input['isDeliveryAddressDifferentFromBilling'] === true && $input['deliveryAddressUuid'] === null) {
            $validationGroups[] = self::VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED;
        }

        if ($currentCustomerUser === null) {
            $validationGroups[] = self::VALIDATION_GROUP_ANONYMOUS_USER;
        }

        return $validationGroups;
    }
}
