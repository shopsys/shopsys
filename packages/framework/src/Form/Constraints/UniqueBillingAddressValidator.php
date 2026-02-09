<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;
use Shopsys\FrameworkBundle\Model\Customer\UniqueBillingAddressChecker;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueBillingAddressValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly UniqueBillingAddressChecker $uniqueBillingAddressChecker,
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueBillingAddress) {
            throw new UnexpectedTypeException($constraint, UniqueBillingAddress::class);
        }

        $billingAddressData = null;
        $domainId = null;

        if ($value instanceof CustomerUserUpdateData) {
            $billingAddressData = $value->billingAddressData;
            $domainId = $value->customerUserData->domainId;
        }

        if ($value instanceof BillingAddressData) {
            $billingAddressData = $value;
            $domainId = $value->customer->getDomainId();
        }

        if ($billingAddressData === null && $domainId === null) {
            $expectedType = sprintf('%s | %s', CustomerUserUpdateData::class, BillingAddressData::class);

            throw new UnexpectedTypeException($value, $expectedType);
        }

        try {
            $this->checkUniqueBillingAddress($billingAddressData, $domainId);
        } catch (BillingAddressCompanyNumberIsNotUniqueException $exception) {
            $domain = $this->domain->getDomainConfigById($domainId);
            $this->context->buildViolation($constraint->message, [
                '{{ company_number }}' => $this->formatValue($billingAddressData->companyNumber),
                '{{ domain_id }}' => $domain->getName(),
            ])
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }

    protected function checkUniqueBillingAddress(
        BillingAddressData $billingAddressData,
        int $domainId,
    ): void {
        $billingAddress = $billingAddressData->id !== null ? $this->billingAddressRepository->getById($billingAddressData->id) : null;

        if ($billingAddress !== null) {
            $this->uniqueBillingAddressChecker->checkUniqueBillingAddressDataIgnoringBillingAddress(
                $billingAddressData,
                $billingAddress,
                $domainId,
            );
        } else {
            $this->uniqueBillingAddressChecker->checkUniqueBillingAddressData($billingAddressData, $domainId);
        }
    }
}
