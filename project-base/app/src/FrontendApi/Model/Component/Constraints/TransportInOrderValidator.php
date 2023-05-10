<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use App\FrontendApi\Model\Transport\TransportValidationFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInOrderValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Transport\TransportValidationFacade $transportValidationFacade
     */
    public function __construct(
        private readonly TransportFacade $transportFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade,
        private readonly TransportValidationFacade $transportValidationFacade
    ) {
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
