<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerUserRoleGroupValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository $customerUserRoleGroupRepository
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CustomerUserRoleGroup) {
            throw new UnexpectedTypeException($constraint, CustomerUserRoleGroup::class);
        }

        $this->checkIfCustomerUserRoleGroupExists($value, $constraint);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroup $constraint
     */
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
