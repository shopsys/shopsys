<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;

class UniqueBillingAddressChecker
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     */
    public function __construct(protected readonly BillingAddressRepository $billingAddressRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param int $domainId
     */
    public function checkUniqueBillingAddressData(BillingAddressData $billingAddressData, int $domainId): void
    {
        $companyNumber = $billingAddressData->companyNumber;

        if ($companyNumber === null) {
            return;
        }

        $billingAddress = $this->billingAddressRepository->findByCompanyNumberAndDomainId($companyNumber, $domainId);

        if ($billingAddress === null) {
            return;
        }

        $message = sprintf('Billing address company number `%s` already exists for domain ID `%d`.', $companyNumber, $domainId);

        throw new BillingAddressCompanyNumberIsNotUniqueException($message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $ignoredBillingAddress
     * @param int $domainId
     */
    public function checkUniqueBillingAddressDataIgnoringBillingAddress(
        BillingAddressData $billingAddressData,
        BillingAddress $ignoredBillingAddress,
        int $domainId,
    ): void {
        $companyNumber = $billingAddressData->companyNumber;

        if ($companyNumber === null) {
            return;
        }

        $billingAddress = $this->billingAddressRepository->findByCompanyNumberAndDomainId($companyNumber, $domainId);

        if ($billingAddress === null) {
            return;
        }

        if ($billingAddress->getId() === $ignoredBillingAddress->getId()) {
            return;
        }

        $message = sprintf('Billing address company number `%s` already exists for domain ID `%d`.', $companyNumber, $domainId);

        throw new BillingAddressCompanyNumberIsNotUniqueException($message);
    }
}
