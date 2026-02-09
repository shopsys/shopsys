<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;

class BillingAddressFacade
{
    public function __construct(
        protected readonly BillingAddressFactory $billingAddressFactory,
        protected readonly BillingAddressRepository $billingAddressRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly UniqueBillingAddressChecker $uniqueBillingAddressChecker,
    ) {
    }

    public function edit(int $billingAddressId, BillingAddressData $billingAddressData): void
    {
        $billingAddress = $this->getById($billingAddressId);

        $domainId = $billingAddressData->customer->getDomainId();
        $this->uniqueBillingAddressChecker->checkUniqueBillingAddressDataIgnoringBillingAddress($billingAddressData, $billingAddress, $domainId);

        $billingAddress->edit($billingAddressData);

        $this->em->flush();
    }

    public function create(BillingAddressData $billingAddressData): BillingAddress
    {
        $domainId = $billingAddressData->customer->getDomainId();
        $this->uniqueBillingAddressChecker->checkUniqueBillingAddressData($billingAddressData, $domainId);

        $billingAddress = $this->billingAddressFactory->create($billingAddressData);

        $this->em->persist($billingAddress);
        $this->em->flush();

        return $billingAddress;
    }

    public function getById(int $billingAddressId): BillingAddress
    {
        return $this->billingAddressRepository->getById($billingAddressId);
    }

    public function getByUuid(string $uuid): BillingAddress
    {
        return $this->billingAddressRepository->getByUuid($uuid);
    }
}
