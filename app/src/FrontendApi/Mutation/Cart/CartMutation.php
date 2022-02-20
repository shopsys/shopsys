<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\AddToCartResult;
use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\FrontendApi\Model\Payment\PaymentInputData;
use App\FrontendApi\Model\Transport\TransportInputData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class CartMutation implements MutationInterface, AliasedInterface
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
     * @var \App\FrontendApi\Model\Cart\CartWatcherFacade
     */
    protected CartWatcherFacade $cartWatcherFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartWatcherFacade $cartWatcherFacade,
        PromoCodeFacade $promoCodeFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->cartWatcherFacade = $cartWatcherFacade;
        $this->promoCodeFacade = $promoCodeFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\AddToCartResult
     */
    public function addToCart(Argument $argument, InputValidator $validator): AddToCartResult
    {
        $validator->validate();

        $input = $argument['input'];

        $productUuid = $input['productUuid'];
        $quantity = $input['quantity'];
        $cartUuid = $input['cartUuid'];
        $isAbsoluteQuantity = $input['isAbsoluteQuantity'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $addProductResult = $this->cartFacade->addProductByUuidToCart(
            $productUuid,
            $quantity,
            $isAbsoluteQuantity,
            $cart
        );

        $transportInputData = $input['transport'] !== null ? new TransportInputData($input['transport']) : null;
        $paymentInputData = $input['payment'] !== null ? new PaymentInputData($input['payment']) : null;

        if ($input['promoCode']) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCode($input['promoCode']);
        } else {
            $promoCode = null;
        }

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart, $transportInputData, $paymentInputData, $promoCode);

        return new AddToCartResult($cartWithModifications, $addProductResult);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult|null
     */
    public function removeFromCart(Argument $argument, InputValidator $validator): ?CartWithModificationsResult
    {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $cartItemUuid = $input['cartItemUuid'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCart($customerUser, $cartUuid);

        $cart = $this->cartFacade->removeItemByUuidFromCart(
            $cartItemUuid,
            $cart
        );

        $transportInputData = $input['transport'] !== null ? new TransportInputData($input['transport']) : null;
        $paymentInputData = $input['payment'] !== null ? new PaymentInputData($input['payment']) : null;

        if ($input['promoCode']) {
            $promoCode = $this->promoCodeFacade->findPromoCodeByCode($input['promoCode']);
        } else {
            $promoCode = null;
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart, $transportInputData, $paymentInputData, $promoCode);
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'addToCart' => 'addToCart',
            'removeFromCart' => 'removeFromCart',
        ];
    }
}
