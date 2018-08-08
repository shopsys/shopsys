<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotSelectedDomainToShowValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }
    
    public function validate(array $values, Constraint $constraint): void
    {
        if (!$constraint instanceof NotSelectedDomainToShow) {
            throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, NotSelectedDomainToShow::class);
        }

        $allDomains = $this->domain->getAll();

        if (count($allDomains) === count($values)) {
            $this->context->addViolation($constraint->message);
            return;
        }
    }
}
