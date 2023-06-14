<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Order\CreateOrderResult;
use App\FrontendApi\Model\Order\CreateOrderResultFactory;
use App\FrontendApi\Mutation\Order\Exception\DeprecatedFieldUserError;
use App\Model\Customer\DeliveryAddress;
use App\Model\Customer\DeliveryAddressFacade;
use App\Model\Customer\User\CustomerUser;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation as BaseCreateOrderMutation;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade;

/**
 * @property \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
 * @property \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @method sendEmail(\App\Model\Order\Order $order)
 */
class CreateOrderMutation extends BaseCreateOrderMutation
{
    public const VALIDATION_GROUP_BEFORE_DEFAULT = 'beforeDefaultValidation';

    /**
     * @param \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\FrontendApi\Model\Order\CreateOrderResultFactory $createOrderResultFactory
     */
    public function __construct(
        OrderDataFactory $orderDataFactory,
        PlaceOrderFacade $placeOrderFacade,
        OrderMailFacade $orderMailFacade,
        private readonly CartFacade $cartFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly DeliveryAddressFacade $deliveryAddressFacade,
        private readonly CartWatcherFacade $cartWatcherFacade,
        private readonly CreateOrderResultFactory $createOrderResultFactory,
    ) {
        parent::__construct($orderDataFactory, $placeOrderFacade, $orderMailFacade);
    }

    /**
     * @deprecated Method is deprecated. Use "createOrderWithResultMutation()" instead.
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\Model\Order\Order
     */
    public function createOrderMutation(Argument $argument, InputValidator $validator): Order
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @throws \Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException
     * @return \App\FrontendApi\Model\Order\CreateOrderResult
     */
    public function createOrderWithResultMutation(Argument $argument, InputValidator $validator): CreateOrderResult
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);

        $input = $argument['input'];
        $this->handleDeprecatedFields($input);
        $cartUuid = $input['cartUuid'];
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            return $this->createOrderResultFactory->getCreateOrderResultByCartWithModifications(
                $cartWithModifications,
            );
        }

        $this->orderDataFactory->updateOrderDataFromCart($orderData, $cart);

        /** @var string|null $deliveryAddressUuid */
        $deliveryAddressUuid = $input['deliveryAddressUuid'];
        $deliveryAddress = $this->resolveDeliveryAddress($deliveryAddressUuid, $customerUser);

        $order = $this->placeOrderFacade->placeOrder(
            $orderData,
            $cart->getQuantifiedProducts(),
            $cart->getFirstAppliedPromoCode(),
            $deliveryAddress,
        );
        $this->cartFacade->deleteCart($cart);

        $this->sendEmail($order);

        return $this->createOrderResultFactory->getCreateOrderResultByOrder($order);
    }

    /**
     * @param string|null $deliveryAddressUuid
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \App\Model\Customer\DeliveryAddress|null
     */
    private function resolveDeliveryAddress(
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
     * @param array $input
     */
    private function handleDeprecatedFields(array $input): void
    {
        if (array_key_exists('products', $input) && $input['products'] !== null) {
            throw new DeprecatedFieldUserError('Usage of "products" input is deprecated, we do not work with this field anymore, the products are taken from the server cart instead.');
        }

        if (array_key_exists('transport', $input) && $input['transport'] !== null) {
            throw new DeprecatedFieldUserError('Usage of "transport" input is deprecated, we do not work with this field anymore, the transport is taken from the server cart instead.');
        }

        if (array_key_exists('payment', $input) && $input['payment'] !== null) {
            throw new DeprecatedFieldUserError('Usage of "payment" input is deprecated, we do not work with this field anymore, the payment is taken from the server cart instead.');
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array|string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        return array_merge([self::VALIDATION_GROUP_BEFORE_DEFAULT], parent::computeValidationGroups($argument));
    }
}
