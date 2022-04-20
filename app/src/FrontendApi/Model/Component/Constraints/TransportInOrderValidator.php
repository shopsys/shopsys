<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Cart\Cart;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInOrderValidator extends ConstraintValidator
{
    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private TransportFacade $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private CurrencyFacade $currencyFacade;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private OrderPreviewFactory $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private TransportPriceCalculation $transportPriceCalculation;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     */
    public function __construct(
        TransportFacade $transportFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade,
        Domain $domain,
        StoreFacade $storeFacade,
        CurrencyFacade $currencyFacade,
        OrderPreviewFactory $orderPreviewFactory,
        TransportPriceCalculation $transportPriceCalculation
    ) {
        $this->transportFacade = $transportFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->domain = $domain;
        $this->storeFacade = $storeFacade;
        $this->currencyFacade = $currencyFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->transportPriceCalculation = $transportPriceCalculation;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TransportInOrder) {
            throw new UnexpectedTypeException($constraint, TransportInOrder::class);
        }
        $cartUuid = $value->cartUuid;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCart($customerUser, $cartUuid);
        $transportInCart = $cart->getTransport();
        if ($transportInCart === null) {
            $this->context->buildViolation($constraint->transportNotSetMessage)
                ->setCode(TransportInOrder::TRANSPORT_NOT_SET_ERROR)
                ->addViolation();
            return;
        }
        if ($this->transportFacade->isTransportVisibleAndEnabledOnCurrentDomain($transportInCart) === false) {
            $this->context->buildViolation($constraint->transportUnavailableMessage)
                ->setCode(TransportInOrder::TRANSPORT_UNAVAILABLE_ERROR)
                ->addViolation();
            return;
        }
        $this->checkTransportPrice($cart, $transportInCart, $customerUser, $constraint);
        $this->checkTransportWeightLimit($transportInCart, $cart, $constraint);
        $this->checkPersonalPickupStoreAvailability($transportInCart, $cart, $constraint);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInOrder $transportInOrderConstraint
     */
    private function checkTransportPrice(
        Cart $cart,
        Transport $transport,
        ?CustomerUser $customerUser,
        TransportInOrder $transportInOrderConstraint
    ): void {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            null,
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode()
        );

        $calculatedTransportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId
        );

        $transportWatchedPrice = $cart->getTransportWatchedPrice();
        $transportPriceChanged = $transportWatchedPrice === null || !$calculatedTransportPrice->getPriceWithVat()->equals($transportWatchedPrice);
        if ($transportPriceChanged) {
            $this->context->buildViolation($transportInOrderConstraint->changedTransportPriceMessage)
                ->setCode(TransportInOrder::CHANGED_TRANSPORT_PRICE_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInOrder $transportInOrderConstraint
     */
    private function checkTransportWeightLimit(Transport $transport, Cart $cart, TransportInOrder $transportInOrderConstraint): void
    {
        $transportWeightLimitExceeded = $transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cart->getTotalWeight();
        if ($transportWeightLimitExceeded) {
            $this->context->buildViolation($transportInOrderConstraint->weightLimitExceeded)
                ->setCode(TransportInOrder::WEIGHT_LIMIT_EXCEEDED_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInOrder $transportInOrderConstraint
     */
    private function checkPersonalPickupStoreAvailability(Transport $transport, Cart $cart, TransportInOrder $transportInOrderConstraint): void
    {
        if ($cart->getPickupPlaceIdentifier() === null || $transport->isPacketery()) {
            return;
        }

        try {
            $this->storeFacade->getByUuidEnabledOnDomain(
                $cart->getPickupPlaceIdentifier(),
                $this->domain->getId()
            );
        } catch (StoreByUuidNotFoundException $e) {
            $this->context->buildViolation($transportInOrderConstraint->pickupPlaceUnavailableMessage)
                ->setCode(TransportInOrder::PICKUP_PLACE_UNAVAILABLE_ERROR)
                ->addViolation();
        }
    }
}
