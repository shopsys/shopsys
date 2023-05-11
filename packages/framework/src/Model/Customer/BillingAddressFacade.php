<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;

class BillingAddressFacade
{
    protected BillingAddressFactory $billingAddressFactory;

    protected BillingAddressRepository $billingAddressRepository;

    protected EntityManagerInterface $em;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactory $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressRepository $billingAddressRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        BillingAddressFactory $billingAddressFactory,
        BillingAddressRepository $billingAddressRepository,
        EntityManagerInterface $em
    ) {
        $this->billingAddressFactory = $billingAddressFactory;
        $this->billingAddressRepository = $billingAddressRepository;
        $this->em = $em;
    }

    /**
     * @param int $billingAddressId
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     */
    public function edit(int $billingAddressId, BillingAddressData $billingAddressData): void
    {
        $billingAddress = $this->getById($billingAddressId);
        $billingAddress->edit($billingAddressData);

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function create(BillingAddressData $billingAddressData): BillingAddress
    {
        $billingAddress = $this->billingAddressFactory->create($billingAddressData);

        $this->em->persist($billingAddress);
        $this->em->flush();

        return $billingAddress;
    }

    /**
     * @param int $billingAddressId
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function getById(int $billingAddressId): BillingAddress
    {
        return $this->billingAddressRepository->getById($billingAddressId);
    }
}
