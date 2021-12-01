<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Cart\CartWatcherFacade;
use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\FrontendApi\Model\Payment\PaymentInputData;
use App\FrontendApi\Model\Transport\TransportInputData;
use App\Model\Order\PromoCode\PromoCodeFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class CartResolver implements ResolverInterface, AliasedInterface
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
    private CartWatcherFacade $cartWatcherFacade;

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
        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartWatcherFacade = $cartWatcherFacade;
        $this->promoCodeFacade = $promoCodeFacade;
    }

    /**
     * @param array $input
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult|null
     */
    public function resolve(array $input): ?CartWithModificationsResult
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->findCart($customerUser, $input['cartUuid']);
        if ($cart === null) {
            return null;
        }
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
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'getCart',
        ];
    }
}
