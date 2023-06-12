<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInOrderValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        private readonly PaymentFacade $paymentFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly CartFacade $cartFacade,
    ) {
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
        }
    }
}
