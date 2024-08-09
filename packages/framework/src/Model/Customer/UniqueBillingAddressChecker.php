<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;

class UniqueBillingAddressChecker
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param int $domainId
     */
    public function checkUniqueBillingAddressData(BillingAddressData $billingAddressData, int $domainId): void
    {
        $companyNumber = $billingAddressData->companyNumber;

        $this->checkUniqueBillingAddressByNumber($companyNumber, $domainId);
    }

    /**
     * @param string|null $companyNumber
     * @param int $domainId
     */
    public function checkUniqueBillingAddressByNumber(?string $companyNumber, int $domainId): void
    {
        $domain = $this->domain->getDomainConfigById($domainId);

        if (!$domain->isB2b()) {
            return;
        }

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

        $this->checkUniqueBillingAddressCompanyNumberIgnoringBillingAddress($companyNumber, $ignoredBillingAddress, $domainId);
    }

    /**
     * @param string|null $companyNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $ignoredBillingAddress
     * @param int $domainId
     */
    public function checkUniqueBillingAddressCompanyNumberIgnoringBillingAddress(
        ?string $companyNumber,
        BillingAddress $ignoredBillingAddress,
        int $domainId,
    ): void {
        $domain = $this->domain->getDomainConfigById($domainId);

        if (!$domain->isB2b()) {
            return;
        }

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
