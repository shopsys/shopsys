<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInOrderValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartFacade
     * @param \Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade $transportValidationFacade
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartFacade,
        protected readonly TransportValidationFacade $transportValidationFacade,
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInOrder $transportInOrder
     */
    protected function checkRequiredPickupPlaceIdentifier(
        Transport $transport,
        ?string $pickupPlaceIdentifier,
        TransportInOrder $transportInOrder,
    ): void {
        try {
            $this->transportValidationFacade->checkRequiredPickupPlaceIdentifier($transport, $pickupPlaceIdentifier);
        } catch (MissingPickupPlaceIdentifierException) {
            $this->context->buildViolation($transportInOrder->missingPickupPlaceIdentifierMessage)
                ->setCode(TransportInOrder::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR)
                ->addViolation();
        }
    }
}
