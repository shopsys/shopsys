<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInCartValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly Domain $domain,
        protected readonly PaymentValidationFacade $paymentValidationFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCart $constraint
     */
    public function validate($value, Constraint $constraint)
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
        } catch (PaymentNotFoundException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCart $constraint
     */
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
}
