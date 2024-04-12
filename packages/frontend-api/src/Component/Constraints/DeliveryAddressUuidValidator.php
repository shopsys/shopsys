<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DeliveryAddressUuidValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DeliveryAddressUuid) {
            throw new UnexpectedTypeException($constraint, DeliveryAddressUuid::class);
        }

        if ($value->deliveryAddressUuid === null || $this->currentCustomerUser->findCurrentCustomerUser() !== null) {
            return;
        }

        $this->context->buildViolation($constraint->loginRequiredErrorMessage)
            ->setCode(DeliveryAddressUuid::LOGIN_REQUIRED_ERROR)
            ->addViolation();
    }
}
