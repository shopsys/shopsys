<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProductInOrderValidator extends ConstraintValidator
{
    /**
     * @var \App\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        CartFacade $cartFacade
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->cartFacade = $cartFacade;
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\ProductInOrder $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductInOrder) {
            throw new UnexpectedTypeException($constraint, ProductInOrder::class);
        }

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $value->cartUuid);

        if ($cart->isEmpty()) {
            $this->context->buildViolation($constraint->noProductInOrderMessage)
                ->setCode($constraint::NO_PRODUCT_IN_ORDER_ERROR)
                ->addViolation();
        }
    }
}
