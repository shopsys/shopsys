<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupAllowEditValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected Security $security,
        protected readonly CustomerFacade $customerFacade,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerUserRoleGroupAllowEdit) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroupAllowEdit::class);
        }

        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return;
        }

        $customerUserGroupRoleToBeSetUuid = $value->roleGroupUuid;
        $editedCustomerUserUuid = $value->customerUserUuid;

        $canEditCustomerUserRoleGroup = $this->canEditCustomerUserRoleGroup(
            $currentCustomerUser,
            $customerUserGroupRoleToBeSetUuid,
            $editedCustomerUserUuid,
        );

        if (!$canEditCustomerUserRoleGroup) {
            $this->context->buildViolation($constraint->message)
                ->setCode(CustomerUserRoleGroupAllowEdit::CUSTOMER_USER_ROLE_GROUP_CANNOT_BE_CHANGED)
                ->addViolation();
        }
    }

    protected function canEditCustomerUserRoleGroup(
        CustomerUser $currentCustomerUser,
        string $customerUserGroupRoleToBeSetUuid,
        string $editedCustomerUserUuid,
    ): bool {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS)) {
            return $this->canEditCustomerUserRoleGroupForRoleApiAll(
                $currentCustomerUser,
                $customerUserGroupRoleToBeSetUuid,
                $editedCustomerUserUuid,
            );
        }

        return $customerUserGroupRoleToBeSetUuid === $currentCustomerUser->getRoleGroup()->getUuid();
    }

    protected function canEditCustomerUserRoleGroupForRoleApiAll(
        CustomerUser $currentCustomerUser,
        string $customerUserGroupRoleToBeSetUuid,
        string $editedCustomerUserUuid,
    ): bool {
        if (
            $currentCustomerUser->getUuid() !== $editedCustomerUserUuid ||
            $customerUserGroupRoleToBeSetUuid === $currentCustomerUser->getRoleGroup()->getUuid()
        ) {
            return true;
        }

        $customer = $currentCustomerUser->getCustomer();

        return $this->customerFacade->hasMultipleCustomerUsersWithDefaultCustomerUserRoleGroup($customer);
    }
}
