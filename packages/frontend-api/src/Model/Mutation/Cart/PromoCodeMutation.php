<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class PromoCodeMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade $cartPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly Domain $domain,
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

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

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

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeCode, $this->domain->getId());

        if ($promoCode !== null) {
            $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }
}
