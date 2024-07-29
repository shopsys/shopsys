<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupAllowEditValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected Security $security,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerUserRoleGroupAllowEdit) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroupAllowEdit::class);
        }

        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return;
        }

        $customerUserGroupRoleUuid = $value;
        $this->canEditCustomerUserRoleGroup($currentCustomerUser, $customerUserGroupRoleUuid, $constraint);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $customerUserGroupRoleUuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAllowEdit $constraint
     */
    protected function canEditCustomerUserRoleGroup(
        CustomerUser $customerUser,
        string $customerUserGroupRoleUuid,
        CustomerUserRoleGroupAllowEdit $constraint,
    ): void {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return;
        }

        if ($customerUserGroupRoleUuid === $customerUser->getRoleGroup()->getUuid()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
