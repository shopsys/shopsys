<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected Security $security,
        protected readonly CustomerFacade $customerFacade,
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
            $this->canEditCustomerUserRoleGroupForRoleApiAll($customerUser, $customerUserGroupRoleUuid, $constraint);

            return;
        }

        if ($customerUserGroupRoleUuid === $customerUser->getRoleGroup()->getUuid()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setCode(CustomerUserRoleGroupAllowEdit::CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED)
            ->addViolation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $customerUserGroupRoleUuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAllowEdit $constraint
     */
    protected function canEditCustomerUserRoleGroupForRoleApiAll(
        CustomerUser $customerUser,
        string $customerUserGroupRoleUuid,
        CustomerUserRoleGroupAllowEdit $constraint,
    ): void {
        if ($customerUserGroupRoleUuid === $customerUser->getRoleGroup()->getUuid()) {
            return;
        }

        $customer = $customerUser->getCustomer();

        if ($this->customerFacade->hasMultipleCustomerUsersWithDefaultCustomerUserRoleGroup($customer)) {
            return;
        }

        $this->context->buildViolation($constraint->messageForLastCustomerUser)
            ->setCode(CustomerUserRoleGroupAllowEdit::LAST_CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED)
            ->addViolation();
    }
}
