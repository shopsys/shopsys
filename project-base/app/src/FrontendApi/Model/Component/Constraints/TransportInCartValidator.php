<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Transport\Exception\InvalidTransportPaymentCombinationException;
use App\FrontendApi\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\FrontendApi\Model\Transport\TransportValidationFacade;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInCartValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Transport\TransportValidationFacade $transportValidationFacade
     */
    public function __construct(
        private TransportFacade $transportFacade,
        private Domain $domain,
        private CurrentCustomerUser $currentCustomerUser,
        private CartFacade $cartFacade,
        private TransportValidationFacade $transportValidationFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInCart $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TransportInCart) {
            throw new UnexpectedTypeException($constraint, TransportInCart::class);
        }
        $transportUuid = $value->transportUuid;
        $pickupPlaceIdentifier = $value->pickupPlaceIdentifier;

        if ($transportUuid === null) {
            return;
        }

        try {
            $transport = $this->transportFacade->getEnabledOnDomainByUuid($transportUuid, $this->domain->getId());
            $this->checkTransportPaymentRelation($transport, $value->cartUuid, $constraint);
            $this->checkRequiredPickupPlaceIdentifier($transport, $pickupPlaceIdentifier, $constraint);
            $this->checkPersonalPickupStoreAvailability($transport, $pickupPlaceIdentifier, $constraint);
            $this->checkTransportWeightLimit($transport, $value->cartUuid, $constraint);
        } catch (TransportNotFoundException $exception) {
            $this->context->buildViolation($constraint->unavailableTransportMessage)
                ->setCode($constraint::UNAVAILABLE_TRANSPORT_ERROR)
                ->atPath('transportUuid')
                ->addViolation();

            return;
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    private function checkRequiredPickupPlaceIdentifier(
        Transport $transport,
        ?string $pickupPlaceIdentifier,
        TransportInCart $transportInCartConstraint,
    ): void {
        try {
            $this->transportValidationFacade->checkRequiredPickupPlaceIdentifier($transport, $pickupPlaceIdentifier);
        } catch (MissingPickupPlaceIdentifierException $exception) {
            $this->context->buildViolation($transportInCartConstraint->missingPickupPlaceIdentifierMessage)
                ->setCode(TransportInCart::MISSING_PICKUP_PLACE_IDENTIFIER_ERROR)
                ->atPath('pickupPlaceIdentifier')
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    private function checkPersonalPickupStoreAvailability(
        Transport $transport,
        ?string $pickupPlaceIdentifier,
        TransportInCart $transportInCartConstraint,
    ): void {
        try {
            $this->transportValidationFacade->checkPersonalPickupStoreAvailability($transport, $pickupPlaceIdentifier);
        } catch (StoreByUuidNotFoundException $exception) {
            $this->context->buildViolation($transportInCartConstraint->unavailablePickupPlaceMessage)
                ->setCode(TransportInCart::UNAVAILABLE_PICKUP_PLACE_ERROR)
                ->atPath('pickupPlaceIdentifier')
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    private function checkTransportWeightLimit(
        Transport $transport,
        ?string $cartUuid,
        TransportInCart $transportInCartConstraint,
    ): void {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        try {
            $this->transportValidationFacade->checkTransportWeightLimit($transport, $cart);
        } catch (TransportWeightLimitExceededException $exception) {
            $this->context->buildViolation($transportInCartConstraint->weightLimitExceededMessage)
                ->setCode(TransportInCart::WEIGHT_LIMIT_EXCEEDED_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @param \App\FrontendApi\Model\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    private function checkTransportPaymentRelation(
        Transport $transport,
        ?string $cartUuid,
        TransportInCart $transportInCartConstraint,
    ): void {
        try {
            $this->transportValidationFacade->checkTransportPaymentRelation($transport, $cartUuid);
        } catch (InvalidTransportPaymentCombinationException $exception) {
            $this->context->buildViolation($transportInCartConstraint->invalidTransportPaymentCombinationMessage)
                ->setCode(TransportInCart::INVALID_TRANSPORT_PAYMENT_COMBINATION_ERROR)
                ->addViolation();
        }
    }
}
