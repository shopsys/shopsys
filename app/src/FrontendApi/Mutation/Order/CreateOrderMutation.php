<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use App\FrontendApi\Exception\ValidationError;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Component\Constraints\PromoCode;
use App\FrontendApi\Mutation\Order\Exception\DeprecatedFieldUserError;
use App\FrontendApi\Mutation\Order\Exception\OrderEmailsNotSentUserError;
use App\FrontendApi\Mutation\Order\Exception\OrderEmptyCartUserError;
use App\Model\Customer\DeliveryAddress;
use App\Model\Customer\DeliveryAddressFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailException;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
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
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private CurrentPromoCodeFacade $currentPromoCodeFacade;

    /**
     * @var \App\Model\Customer\DeliveryAddressFacade
     */
    private DeliveryAddressFacade $deliveryAddressFacade;

    /**
     * @param \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     */
    public function __construct(
        OrderDataFactory $orderDataFactory,
        PlaceOrderFacade $placeOrderFacade,
        OrderMailFacade $orderMailFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        DeliveryAddressFacade $deliveryAddressFacade
    ) {
        parent::__construct($orderDataFactory, $placeOrderFacade, $orderMailFacade);

        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->deliveryAddressFacade = $deliveryAddressFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\Model\Order\Order
     */
    public function createOrder(Argument $argument, InputValidator $validator): Order
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
        $this->orderDataFactory->updateOrderDataFromCart($orderData, $cart);

        $quantifiedProducts = $cart->getQuantifiedProducts();
        if (count($quantifiedProducts) === 0) {
            throw new OrderEmptyCartUserError('There are no products in the cart.');
        }

        $promoCode = $cart->getFirstAppliedPromoCode();
        if ($promoCode !== null) {
            try {
                $this->currentPromoCodeFacade->getValidatedPromoCode($promoCode->getCode(), $cart);
            } catch (PromoCodeException $exception) {
                throw new ValidationError($exception->getMessage(), PromoCode::INVALID_ERROR, 'input.promoCode');
            }
        }

        /** @var string|null $deliveryAddressUuid */
        $deliveryAddressUuid = $input['deliveryAddressUuid'];
        $deliveryAddress = $this->resolveDeliveryAddress($deliveryAddressUuid, $customerUser);

        $order = $this->placeOrderFacade->placeOrder($orderData, $quantifiedProducts, $promoCode, $deliveryAddress);
        $this->cartFacade->deleteCart($cart);

        try {
            $this->sendEmail($order);
        } catch (MailException $e) {
            throw new OrderEmailsNotSentUserError('Unable to send some emails, please contact us for order verification.');
        }

        return $order;
    }

    /**
     * @param string|null $deliveryAddressUuid
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \App\Model\Customer\DeliveryAddress|null
     */
    private function resolveDeliveryAddress(?string $deliveryAddressUuid, ?CustomerUser $customerUser): ?DeliveryAddress
    {
        if ($deliveryAddressUuid === null || $customerUser === null) {
            return null;
        }

        return $this->deliveryAddressFacade->findByUuidAndCustomer(
            $deliveryAddressUuid,
            $customerUser->getCustomer()
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
}
