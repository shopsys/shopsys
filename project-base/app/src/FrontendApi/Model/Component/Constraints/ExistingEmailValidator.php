<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExistingEmailValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(Domain $domain, CustomerUserFacade $customerUserFacade)
    {
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param string $value
     * @param \App\FrontendApi\Model\Component\Constraints\ExistingEmail $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExistingEmail) {
            throw new UnexpectedTypeException($constraint, ExistingEmail::class);
        }
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($value, $this->domain->getId());
        if ($customerUser === null) {
            $this->context->buildViolation($constraint->invalidMessage)
                ->setCode($constraint::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR)
                ->addViolation();
        }
    }
}
