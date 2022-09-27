<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Cart;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\FrontendApi\Model\Transport\TransportValidationFacade;
use App\Model\Cart\Cart;
use App\Model\Payment\PaymentFacade;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Transport\TransportFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class OrderStepsAccessResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\FrontendApi\Model\Transport\TransportValidationFacade $transportValidationFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade,
        private readonly TransportFacade $transportFacade,
        private readonly TransportValidationFacade $transportValidationFacade,
        private readonly PaymentFacade $paymentFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array{canAccessTransportAndPayment: bool, canAccessContactInformation: bool}
     */
    public function resolve(Argument $argument): array
    {
        $input = CartInputDefaultValueInitializer::initializeDefaultValues($argument);

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->cartFacade->findCart($customerUser, $input['cartUuid']);

        if ($cart === null) {
            return [
                'canAccessTransportAndPayment' => false,
                'canAccessContactInformation' => false,
            ];
        }

        return [
            'canAccessTransportAndPayment' => !$cart->isEmpty(),
            'canAccessContactInformation' => !$cart->isEmpty() && $this->isContactInfoAccessible($cart),
        ];
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @return bool
     */
    private function isContactInfoAccessible(Cart $cart): bool
    {
        if ($cart->getTransport() === null || $cart->getPayment() === null) {
            return false;
        }

        try {
            $this->transportValidationFacade->checkTransportWeightLimit($cart->getTransport(), $cart);
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($cart->getTransport(), $cart->getPickupPlaceIdentifier());
            $transportIsAccessible = $this->transportFacade->isTransportVisibleAndEnabledOnCurrentDomain($cart->getTransport());
            $paymentIsAccessible = $this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($cart->getPayment());
        } catch (TransportWeightLimitExceededException|StoreByUuidNotFoundException $exception) {
            return false;
        }

        return $transportIsAccessible && $paymentIsAccessible;
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'checkOrderStepsAccessibility',
        ];
    }
}
