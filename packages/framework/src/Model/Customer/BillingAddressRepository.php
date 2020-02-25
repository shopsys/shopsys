<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class BillingAddressRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBillingAddressRepository(): EntityRepository
    {
        return $this->em->getRepository(BillingAddress::class);
    }

    /**
     * @param int $billingAddressId
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function getById(int $billingAddressId): BillingAddress
    {
        $billingAddress = $this->getBillingAddressRepository()->find($billingAddressId);

        if ($billingAddress === null) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressNotFoundException('Billing address with ID ' . $billingAddressId . ' not found.');
        }

        return $billingAddress;
    }
}
