<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotSelectedDomainToShowValidator extends ConstraintValidator
{
    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param array $values
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof NotSelectedDomainToShow) {
            throw new UnexpectedTypeException($constraint, NotSelectedDomainToShow::class);
        }

        $allDomains = $this->domain->getAll();

        if (count($allDomains) === count($values)) {
            $this->context->addViolation($constraint->message);

            return;
        }
    }
}
