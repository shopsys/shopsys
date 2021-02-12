<?php

declare(strict_types=1);

namespace App\Form\Constraints;

use App\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueCollection;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmailValidator as BaseUniqueEmailValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEmailValidator extends BaseUniqueEmailValidator
{
    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CustomerUserFacade $customerUserFacade,
        Domain $domain
    ) {
        parent::__construct($customerUserFacade, $domain);
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
                ]
            );
        }
    }
}
