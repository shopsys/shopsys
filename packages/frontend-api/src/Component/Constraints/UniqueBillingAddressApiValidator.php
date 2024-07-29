<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Overblog\GraphQLBundle\Validator\ValidationNode;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;
use Shopsys\FrameworkBundle\Model\Customer\UniqueBillingAddressChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueBillingAddressApiValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UniqueBillingAddressChecker $uniqueBillingAddressChecker
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly UniqueBillingAddressChecker $uniqueBillingAddressChecker,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueBillingAddressApi) {
            throw new UnexpectedTypeException($constraint, UniqueBillingAddressApi::class);
        }

        try {
            $this->checkUniqueBillingAddress($value);
        } catch (BillingAddressCompanyNumberIsNotUniqueException $exception) {
            $this->context->buildViolation($constraint->message, [
                '{{ company_number }}' => $this->formatValue($value->companyNumber),
            ])
                ->addViolation();
        }
    }

    /**
     * @param mixed $billingAddressApiData
     */
    protected function checkUniqueBillingAddress(
        mixed $billingAddressApiData,
    ): void {
        $domainId = $this->domain->getId();
        $this->uniqueBillingAddressChecker->checkUniqueBillingAddressByNumber($billingAddressApiData->companyNumber, $domainId);
    }
}
