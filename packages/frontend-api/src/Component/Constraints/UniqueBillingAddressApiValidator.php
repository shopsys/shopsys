<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;
use Shopsys\FrameworkBundle\Model\Customer\UniqueBillingAddressChecker;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueBillingAddressApiValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly UniqueBillingAddressChecker $uniqueBillingAddressChecker,
        protected readonly Domain $domain,
        protected readonly BillingAddressRepository $billingAddressRepository,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
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
                ->setCode($constraint::DUPLICATE_BILLING_ADDRESS)
                ->addViolation();
        }
    }

    protected function checkUniqueBillingAddress(mixed $billingAddressApiData): void
    {
        $billingAddress = null;
        $domainId = $this->domain->getId();
        $companyNumber = $billingAddressApiData->companyNumber;

        if ($billingAddressApiData->billingAddressUuid !== null) {
            $billingAddress = $this->billingAddressRepository->getByUuid($billingAddressApiData->billingAddressUuid);
        }

        if ($billingAddress !== null) {
            $this->uniqueBillingAddressChecker->checkUniqueBillingAddressCompanyNumberIgnoringBillingAddress(
                $companyNumber,
                $billingAddress,
                $domainId,
            );
        } else {
            $this->uniqueBillingAddressChecker->checkUniqueBillingAddressByNumber($companyNumber, $domainId);
        }
    }
}
