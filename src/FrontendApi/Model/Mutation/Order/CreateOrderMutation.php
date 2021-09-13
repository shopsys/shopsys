<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation\Order;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Order\PromoCode\PromoCodeFacade;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Mail\Exception\MailException;
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
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @param \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(
        OrderDataFactory $orderDataFactory,
        PlaceOrderFacade $placeOrderFacade,
        OrderMailFacade $orderMailFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        PromoCodeFacade $promoCodeFacade
    ) {
        parent::__construct($orderDataFactory, $placeOrderFacade, $orderMailFacade);

        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->promoCodeFacade = $promoCodeFacade;
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
        if (array_key_exists('products', $input) && $input['products'] !== null) {
            throw new UserError('Usage of "products" input is deprecated, we do not work with this field anymore, the products are taken from the server cart instead.');
        }
        $cartUuid = $input['cartUuid'];
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCart($customerUser, $cartUuid);
        $quantifiedProducts = $cart->getQuantifiedProducts();
        if (count($quantifiedProducts) === 0) {
            throw new UserError('There are no products in the cart.');
        }

        $promoCode = null;
        if (isset($input['promoCode'])) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCode($input['promoCode']);
        }

        $order = $this->placeOrderFacade->placeOrder($orderData, $quantifiedProducts, $promoCode);
        $this->cartFacade->deleteCart($cart);

        try {
            $this->sendEmail($order);
        } catch (MailException $e) {
            throw new UserError('Unable to send some emails, please contact us for order verification.');
        }

        return $order;
    }
}
