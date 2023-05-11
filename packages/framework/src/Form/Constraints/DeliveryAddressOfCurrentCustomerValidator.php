<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DeliveryAddressOfCurrentCustomerValidator extends ConstraintValidator
{
    protected CurrentCustomerUser $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param mixed $deliveryAddress
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($deliveryAddress, Constraint $constraint): void
    {
        if (!$constraint instanceof DeliveryAddressOfCurrentCustomer) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        if ($deliveryAddress === null) {
            return;
        }

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (
            $customerUser === null
            || !in_array($deliveryAddress, $customerUser->getCustomer()->getDeliveryAddresses(), true)
        ) {
            $this->context->addViolation($constraint->message);
        }
    }
}
