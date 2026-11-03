<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CurrentCustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupAssignableValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly CurrentCustomerUserRoleResolver $currentCustomerUserRoleResolver,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerUserRoleGroupAssignable) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroupAssignable::class);
        }

        try {
            $customerUserRoleGroup = $this->customerUserRoleGroupRepository->getByUuid($value);
        } catch (CustomerUserRoleGroupNotFoundException) {
            return;
        }

        if (!$this->currentCustomerUserRoleResolver->canCurrentCustomerUserAssignRoleGroup($customerUserRoleGroup)) {
            $this->context->buildViolation($constraint->message)
                ->setCode(CustomerUserRoleGroupAssignable::CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE)
                ->addViolation();
        }
    }
}
