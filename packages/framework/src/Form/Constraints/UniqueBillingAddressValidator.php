<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UniqueBillingAddressChecker $uniqueBillingAddressChecker
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly UniqueBillingAddressChecker $uniqueBillingAddressChecker,
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueBillingAddress) {
            throw new UnexpectedTypeException($constraint, UniqueBillingAddress::class);
        }

        if (!$value instanceof CustomerUserUpdateData) {
            throw new UnexpectedTypeException($value, CustomerUserUpdateData::class);
        }

        $billingAddressData = $value->billingAddressData;
        $domainId = $value->customerUserData->domainId;

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param int $domainId
     * @throws \Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException
     */
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
