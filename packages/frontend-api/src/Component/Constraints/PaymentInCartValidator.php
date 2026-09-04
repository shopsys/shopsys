<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\PaymentUnavailableForRemainingAmountToPayInCartException;
use Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInCartValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly PaymentValidationFacade $paymentValidationFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCart $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentInCart) {
            throw new UnexpectedTypeException($constraint, PaymentInCart::class);
        }
        $paymentUuid = $value->paymentUuid;

        if ($paymentUuid === null) {
            return;
        }

        try {
            $payment = $this->paymentFacade->getEnabledOnDomainByUuid($paymentUuid, $this->domain->getId());
            $this->checkPaymentTransportRelation($payment, $value->cartUuid, $constraint);
            $this->checkPaymentSuitabilityForRemainingAmountToPay($payment, $value->cartUuid, $constraint);
        } catch (PaymentNotFoundException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }

    protected function checkPaymentTransportRelation(
        Payment $payment,
        ?string $cartUuid,
        PaymentInCart $constraint,
    ): void {
        try {
            $this->paymentValidationFacade->checkPaymentTransportRelation($payment, $cartUuid);
        } catch (InvalidPaymentTransportCombinationException $exception) {
            $this->context->buildViolation($constraint->invalidPaymentTransportCombinationMessage)
                ->setCode($constraint::INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR)
                ->addViolation();
        }
    }

    protected function checkPaymentSuitabilityForRemainingAmountToPay(
        Payment $payment,
        ?string $cartUuid,
        PaymentInCart $constraint,
    ): void {
        try {
            $this->paymentValidationFacade->checkPaymentSuitabilityForRemainingAmountToPayInCart($payment, $cartUuid);
        } catch (PaymentUnavailableForRemainingAmountToPayInCartException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }
}
