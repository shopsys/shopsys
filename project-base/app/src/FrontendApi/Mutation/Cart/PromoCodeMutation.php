<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class PromoCodeMutation extends AbstractMutation
{
    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade $cartPromoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartWatcherFacade $cartWatcherFacade,
        private readonly CartPromoCodeFacade $cartPromoCodeFacade,
        private readonly PromoCodeFacade $promoCodeFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function applyPromoCodeToCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $promoCodeCode = $input['promoCode'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $this->cartPromoCodeFacade->applyPromoCodeByCode($cart, $promoCodeCode);

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function removePromoCodeFromCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $promoCodeCode = $input['promoCode'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $promoCode = $this->promoCodeFacade->findPromoCodeByCode($promoCodeCode);

        if ($promoCode !== null) {
            $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
