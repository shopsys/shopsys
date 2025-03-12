<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\Model\Customer\User\CustomerUserPasswordFacade;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundByEmailAndDomainException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ResetPasswordHashValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\ResetPasswordHash $constraint
     */
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ResetPasswordHash) {
            throw new UnexpectedTypeException($constraint, ResetPasswordHash::class);
        }

        $email = $value->email;
        $hash = $value->hash;

        try {
            if (!$this->customerUserPasswordFacade->isResetPasswordHashValid($email, $this->domain->getId(), $hash)) {
                $this->context->buildViolation($constraint->invalidMessage)
                    ->setCode($constraint::INVALID_HASH_ERROR)
                    ->atPath('hash')
                    ->addViolation();
            }
        } catch (CustomerUserNotFoundByEmailAndDomainException) {
            /** No need to do anything, already handled by @see \App\FrontendApi\Model\Component\Constraints\ExistingEmailValidator */
            return;
        }
    }
}
