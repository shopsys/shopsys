<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProductInOrderValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\ProductInOrder $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductInOrder) {
            throw new UnexpectedTypeException($constraint, ProductInOrder::class);
        }

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $value->cartUuid);

        if ($cart->isEmpty()) {
            $this->context->buildViolation($constraint->noProductInOrderMessage)
                ->setCode($constraint::NO_PRODUCT_IN_ORDER_ERROR)
                ->addViolation();
        }
    }
}
