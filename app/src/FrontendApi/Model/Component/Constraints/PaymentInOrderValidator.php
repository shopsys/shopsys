<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Payment\Exception\PaymentPriceChangedException;
use App\FrontendApi\Model\Payment\PaymentValidationFacade;
use App\Model\Cart\Cart;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInOrderValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\FrontendApi\Model\Payment\PaymentValidationFacade
     */
    private PaymentValidationFacade $paymentValidationFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Payment\PaymentValidationFacade $paymentValidationFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade,
        PaymentValidationFacade $paymentValidationFacade
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
        $this->paymentValidationFacade = $paymentValidationFacade;
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\PaymentInOrder $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PaymentInOrder) {
            throw new UnexpectedTypeException($constraint, PaymentInOrder::class);
        }
        $cartUuid = $value->cartUuid;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $paymentInCart = $cart->getPayment();
        if ($paymentInCart === null) {
            $this->context->buildViolation($constraint->paymentNotSetMessage)
                ->setCode($constraint::PAYMENT_NOT_SET_ERROR)
                ->addViolation();
            return;
        }
        if ($this->paymentFacade->isPaymentVisibleAndEnabledOnCurrentDomain($paymentInCart) === false) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->addViolation();
            return;
        }
        $this->checkPaymentPrice($paymentInCart, $cart, $constraint);
    }

    /**
     * @param \App\Model\Payment\Payment $paymentInCart
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Component\Constraints\PaymentInOrder $constraint
     */
    private function checkPaymentPrice(Payment $paymentInCart, Cart $cart, PaymentInOrder $constraint): void
    {
        try {
            $this->paymentValidationFacade->checkPaymentPrice($paymentInCart, $cart);
        } catch (PaymentPriceChangedException $exception) {
            $this->context->buildViolation($constraint->changedPaymentPriceMessage)
                ->setCode($constraint::CHANGED_PAYMENT_PRICE_ERROR)
                ->addViolation();
        }
    }
}
