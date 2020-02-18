<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DeliveryAddressOfCurrentCustomerValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

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
            throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        if ($deliveryAddress !== null) {
            $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

            if ($customerUser !== null) {
                if (!in_array($deliveryAddress, $customerUser->getCustomer()->getDeliveryAddresses(), true)) {
                    $this->context->addViolation($constraint->message);
                }
            } else {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
