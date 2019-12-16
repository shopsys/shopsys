<?php

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CustomerUserFacade $customerUserFacade,
        Domain $domain
    ) {
        $this->customerUserFacade = $customerUserFacade;
        $this->domain = $domain;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueEmail) {
            throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, UniqueCollection::class);
        }

        $email = (string)$value;

        $domainId = $constraint->domainId ?? $this->domain->getId();

        if ($constraint->ignoredEmail != $value && $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId) !== null) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '{{ email }}' => $email,
                ]
            );
        }
    }
}
