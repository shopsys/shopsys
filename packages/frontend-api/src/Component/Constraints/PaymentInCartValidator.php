<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
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
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
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
            $cart = $this->cartApiFacade->getCartCreateIfNotExists($this->currentCustomerUser->findCurrentCustomerUser(), $value->cartUuid);
            $this->checkPaymentTransportRelation($payment, $cart, $constraint);
            $this->checkPaymentSuitabilityForRemainingAmountToPay($payment, $cart, $constraint);
        } catch (PaymentNotFoundException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }

    protected function checkPaymentTransportRelation(
        Payment $payment,
        Cart $cart,
        PaymentInCart $constraint,
    ): void {
        try {
            $this->paymentValidationFacade->checkPaymentTransportRelation($payment, $cart);
        } catch (InvalidPaymentTransportCombinationException $exception) {
            $this->context->buildViolation($constraint->invalidPaymentTransportCombinationMessage)
                ->setCode($constraint::INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR)
                ->addViolation();
        }
    }

    protected function checkPaymentSuitabilityForRemainingAmountToPay(
        Payment $payment,
        Cart $cart,
        PaymentInCart $constraint,
    ): void {
        try {
            $this->paymentValidationFacade->checkPaymentSuitabilityForRemainingAmountToPay($payment, $cart);
        } catch (PaymentUnavailableForRemainingAmountToPayInCartException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }
}
