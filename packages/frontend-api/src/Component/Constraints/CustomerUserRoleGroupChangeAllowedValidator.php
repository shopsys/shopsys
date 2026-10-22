<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CurrentCustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupChangeAllowedValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly CurrentCustomerUserRoleResolver $currentCustomerUserRoleResolver,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerUserRoleGroupChangeAllowed) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroupChangeAllowed::class);
        }

        try {
            $roleGroupToAssign = $this->customerUserRoleGroupRepository->getByUuid($value->roleGroupUuid);
            $editedCustomerUser = $this->findEditedCustomerUser($value->customerUserUuid);
        } catch (CustomerUserNotFoundException | CustomerUserRoleGroupNotFoundException) {
            return;
        }

        if ($editedCustomerUser === null) {
            $this->addRoleGroupChangeNotAllowedViolation($constraint);

            return;
        }

        if ($editedCustomerUser->getRoleGroup()->getUuid() === $roleGroupToAssign->getUuid()) {
            return;
        }

        if (
            !$this->currentCustomerUserRoleResolver->canCurrentCustomerUserChangeRoleGroupOfCustomerUser($editedCustomerUser)
            || !$this->currentCustomerUserRoleResolver->canCurrentCustomerUserAssignRoleGroup($roleGroupToAssign)
        ) {
            $this->addRoleGroupChangeNotAllowedViolation($constraint);
        }
    }

    protected function findEditedCustomerUser(?string $customerUserUuid): ?CustomerUser
    {
        if ($customerUserUuid === null) {
            return $this->currentCustomerUser->findCurrentCustomerUser();
        }

        return $this->customerUserFacade->getByUuid($customerUserUuid);
    }

    protected function addRoleGroupChangeNotAllowedViolation(CustomerUserRoleGroupChangeAllowed $constraint): void
    {
        $this->context->buildViolation($constraint->message)
            ->atPath('roleGroupUuid')
            ->setCode(CustomerUserRoleGroupChangeAllowed::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED)
            ->addViolation();
    }
}
