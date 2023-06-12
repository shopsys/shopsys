<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Payment\Exception\InvalidPaymentTransportCombinationException;
use App\FrontendApi\Model\Payment\PaymentValidationFacade;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInCartValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     */
    public function __construct(
        private PaymentFacade $paymentFacade,
        private Domain $domain,
        private PaymentValidationFacade $paymentValidationFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\PaymentInCart $constraint
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
     * @param \App\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     * @param \App\FrontendApi\Model\Component\Constraints\PaymentInCart $constraint
     */
    private function checkPaymentTransportRelation(
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
