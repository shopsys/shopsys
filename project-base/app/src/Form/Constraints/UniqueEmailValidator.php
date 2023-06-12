<?php

declare(strict_types=1);

namespace App\Form\Constraints;

use Shopsys\FrameworkBundle\Form\Constraints\UniqueCollection;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmailValidator as BaseUniqueEmailValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class UniqueEmailValidator extends BaseUniqueEmailValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        $email = (string)$value;
        $domainId = $constraint->domainId ?? $this->domain->getId();
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId);

        if ($constraint->ignoredEmail !== $value
            && $customerUser !== null
            && $customerUser->isActivated() === true
        ) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ email }}' => $email,
                ],
            );
        }
    }
}
