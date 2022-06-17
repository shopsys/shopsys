<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use App\FrontendApi\Model\Transport\Exception\TransportPriceChangedException;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\FrontendApi\Model\Transport\TransportValidationFacade;
use App\Model\Cart\Cart;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInOrderValidator extends ConstraintValidator
{
    /**
     * @var \App\FrontendApi\Model\Transport\TransportValidationFacade
     */
    private TransportValidationFacade $transportValidationFacade;

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
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Transport\TransportValidationFacade $transportValidationFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade,
        TransportValidationFacade $transportValidationFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->transportValidationFacade = $transportValidationFacade;
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
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
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
        $this->checkRequiredPickupPlaceIdentifier($transportInCart, $cart->getPickupPlaceIdentifier(), $constraint);
        $this->checkTransportPrice($cart, $transportInCart, $constraint);
        $this->checkTransportWeightLimit($transportInCart, $cart, $constraint);
        $this->checkPersonalPickupStoreAvailability($transportInCart, $cart, $constraint);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Transport\Transport $transport
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInOrder $transportInOrderConstraint
     */
    private function checkTransportPrice(
        Cart $cart,
        Transport $transport,
        TransportInOrder $transportInOrderConstraint
    ): void {
        try {
            $this->transportValidationFacade->checkTransportPrice($transport, $cart);
        } catch (TransportPriceChangedException $exception) {
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
        try {
            $this->transportValidationFacade->checkTransportWeightLimit($transport, $cart);
        } catch (TransportWeightLimitExceededException $exception) {
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
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $cart->getPickupPlaceIdentifier());
        } catch (StoreByUuidNotFoundException $e) {
            $this->context->buildViolation($transportInOrderConstraint->pickupPlaceUnavailableMessage)
                ->setCode(TransportInOrder::PICKUP_PLACE_UNAVAILABLE_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInOrder $transportInOrder
     */
    private function checkRequiredPickupPlaceIdentifier(Transport $transport, ?string $pickupPlaceIdentifier, TransportInOrder $transportInOrder): void
    {
        try {
            $this->transportValidationFacade->checkRequiredPickupPlaceIdentifier($transport, $pickupPlaceIdentifier);
        } catch (MissingPickupPlaceIdentifierException $exception) {
            $this->context->buildViolation($transportInOrder->missingPickupPlaceIdentifierMessage)
                ->setCode(TransportInOrder::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR)
                ->addViolation();
        }
    }
}
