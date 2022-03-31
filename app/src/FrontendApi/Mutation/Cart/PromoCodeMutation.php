<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Cart\CartPromoCodeFacade;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class PromoCodeMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\Model\Cart\CartPromoCodeFacade
     */
    private CartPromoCodeFacade $cartPromoCodeFacade;

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
    private CartWatcherFacade $cartWatcherFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Cart\CartPromoCodeFacade $cartPromoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartWatcherFacade $cartWatcherFacade,
        CartPromoCodeFacade $cartPromoCodeFacade,
        PromoCodeFacade $promoCodeFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->cartWatcherFacade = $cartWatcherFacade;
        $this->cartPromoCodeFacade = $cartPromoCodeFacade;
        $this->promoCodeFacade = $promoCodeFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function applyPromoCodeToCart(Argument $argument, InputValidator $validator): CartWithModificationsResult
    {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $promoCodeCode = $input['promoCode'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCart($customerUser, $cartUuid);

        $this->cartPromoCodeFacade->applyPromoCodeByCode($cart, $promoCodeCode);

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function removePromoCodeFromCart(Argument $argument, InputValidator $validator): CartWithModificationsResult
    {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $promoCodeCode = $input['promoCode'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCart($customerUser, $cartUuid);

        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($promoCodeCode);
        if ($promoCode !== null) {
            $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'applyPromoCodeToCart' => 'applyPromoCodeToCart',
            'removePromoCodeFromCart' => 'removePromoCodeFromCart',
        ];
    }
}
