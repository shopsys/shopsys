<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentTransportRelationValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentTransportRelation) {
            throw new UnexpectedTypeException($constraint, PaymentTransportRelation::class);
        }
        $cartUuid = $value->cartUuid;
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $transportInCart = $cart->getTransport();
        $paymentInCart = $cart->getPayment();

        if ($transportInCart === null || $paymentInCart === null) {
            return;
        }

        $relationExists = in_array($transportInCart, $paymentInCart->getTransports(), true);

        if (!$relationExists) {
            $this->context->buildViolation($constraint->invalidCombinationMessage)
                ->setCode(PaymentTransportRelation::INVALID_COMBINATION_ERROR)
                ->addViolation();
        }
    }
}
