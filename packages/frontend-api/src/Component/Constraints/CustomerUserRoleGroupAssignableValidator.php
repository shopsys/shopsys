<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupAssignableValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
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

        if (!$this->isAssignableByCurrentCustomerUser($customerUserRoleGroup)) {
            $this->context->buildViolation($constraint->message)
                ->setCode(CustomerUserRoleGroupAssignable::CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE)
                ->addViolation();
        }
    }

    protected function isAssignableByCurrentCustomerUser(CustomerUserRoleGroup $customerUserRoleGroup): bool
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return false;
        }

        return $this->customerUserRoleResolver->canCustomerUserAssignRoleGroup($currentCustomerUser, $customerUserRoleGroup);
    }
}
