<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreByUuidNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\InvalidTransportPaymentCombinationException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportWeightLimitExceededException;
use Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportInCartValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Transport\TransportValidationFacade $transportValidationFacade
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly TransportValidationFacade $transportValidationFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart $constraint
     */
    public function validate($value, Constraint $constraint)
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    protected function checkRequiredPickupPlaceIdentifier(
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    protected function checkPersonalPickupStoreAvailability(
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    protected function checkTransportWeightLimit(
        Transport $transport,
        ?string $cartUuid,
        TransportInCart $transportInCartConstraint,
    ): void {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        try {
            $this->transportValidationFacade->checkTransportWeightLimit($transport, $cart);
        } catch (TransportWeightLimitExceededException $exception) {
            $this->context->buildViolation($transportInCartConstraint->weightLimitExceededMessage)
                ->setCode(TransportInCart::WEIGHT_LIMIT_EXCEEDED_ERROR)
                ->addViolation();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\TransportInCart $transportInCartConstraint
     */
    protected function checkTransportPaymentRelation(
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
