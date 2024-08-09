<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressNotFoundException;

class BillingAddressRepository
{
    protected EntityManagerInterface $em;

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
            throw new BillingAddressNotFoundException('Billing address with ID ' . $billingAddressId . ' not found.');
        }

        return $billingAddress;
    }

    /**
     * @param string $companyNumber
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress|null
     */
    public function findByCompanyNumberAndDomainId(string $companyNumber, int $domainId): ?BillingAddress
    {
        return $this->getBillingAddressRepository()->createQueryBuilder('ba')
            ->join('ba.customer', 'c')
            ->where('ba.companyNumber = :companyNumber')
            ->andWhere('c.domainId = :domainId')
            ->setParameter('companyNumber', $companyNumber)
            ->setParameter('domainId', $domainId)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function getByUuid(string $uuid): BillingAddress
    {
        $billingAddress = $this->getBillingAddressRepository()->findOneBy(['uuid' => $uuid]);

        if ($billingAddress === null) {
            throw new BillingAddressNotFoundException('Billing address with UUID ' . $uuid . ' not found.');
        }

        return $billingAddress;
    }
}
