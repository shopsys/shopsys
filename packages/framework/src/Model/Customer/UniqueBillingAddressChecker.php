<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressCompanyNumberIsNotUniqueException;

class UniqueBillingAddressChecker
{
    public function __construct(
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly Domain $domain,
    ) {
    }

    public function checkUniqueBillingAddressData(BillingAddressData $billingAddressData, int $domainId): void
    {
        $companyNumber = $billingAddressData->companyNumber;

        $this->checkUniqueBillingAddressByNumber($companyNumber, $domainId);
    }

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

    public function checkUniqueBillingAddressDataIgnoringBillingAddress(
        BillingAddressData $billingAddressData,
        BillingAddress $ignoredBillingAddress,
        int $domainId,
    ): void {
        $companyNumber = $billingAddressData->companyNumber;

        $this->checkUniqueBillingAddressCompanyNumberIgnoringBillingAddress($companyNumber, $ignoredBillingAddress, $domainId);
    }

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
