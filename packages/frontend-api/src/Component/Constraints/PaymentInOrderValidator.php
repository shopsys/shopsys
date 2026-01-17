<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInOrderValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\PaymentInOrder $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentInOrder) {
            throw new UnexpectedTypeException($constraint, PaymentInOrder::class);
        }
        $cartUuid = $value->cartUuid;
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
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
