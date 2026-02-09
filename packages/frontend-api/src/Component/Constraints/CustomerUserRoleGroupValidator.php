<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerUserRoleGroup) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroup::class);
        }

        $this->checkIfCustomerUserRoleGroupExists($value, $constraint);
    }

    protected function checkIfCustomerUserRoleGroupExists(string $uuid, CustomerUserRoleGroup $constraint): void
    {
        try {
            $this->customerUserRoleGroupRepository->getByUuid($uuid);
        } catch (CustomerUserRoleGroupNotFoundException $e) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
