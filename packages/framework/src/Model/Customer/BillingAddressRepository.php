<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\BillingAddressNotFoundException;

class BillingAddressRepository
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getBillingAddressRepository(): EntityRepository
    {
        return $this->em->getRepository(BillingAddress::class);
    }

    public function getById(int $billingAddressId): BillingAddress
    {
        $billingAddress = $this->getBillingAddressRepository()->find($billingAddressId);

        if ($billingAddress === null) {
            throw new BillingAddressNotFoundException('Billing address with ID ' . $billingAddressId . ' not found.');
        }

        return $billingAddress;
    }

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

    public function getByUuid(string $uuid): BillingAddress
    {
        $billingAddress = $this->getBillingAddressRepository()->findOneBy(['uuid' => $uuid]);

        if ($billingAddress === null) {
            throw new BillingAddressNotFoundException('Billing address with UUID ' . $uuid . ' not found.');
        }

        return $billingAddress;
    }
}
