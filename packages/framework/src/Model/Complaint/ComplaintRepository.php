<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class ComplaintRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getComplaintRepository(): EntityRepository
    {
        return $this->em->getRepository(Complaint::class);
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getComplaintsQueryBuilder(string $locale): QueryBuilder
    {
        $subQuery = $this->em->createQueryBuilder()
            ->select('bad.id')
            ->from(BillingAddress::class, 'bad')
            ->where('bad.customer = c')
            ->orderBy('bad.id', 'ASC')
            ->setMaxResults(1);

        $queryBuilder = $this->getComplaintRepository()->createQueryBuilder('cmp');

        return $queryBuilder
            ->select('cmp')
            ->addSelect('o.number as orderNumber')
            ->addSelect('o.id as orderId')
            ->addSelect('cu.id as customerUserId')
            ->join('cmp.order', 'o')
            ->addSelect(
                '(CASE WHEN ba.companyName IS NOT NULL
                    THEN CONCAT(ba.companyName, \' - \', cu.lastName, \' \', cu.firstName)
                    ELSE CONCAT(cu.lastName, \' \', cu.firstName)
                END) AS customerName',
            )
            ->addSelect('MAX(cst.name) AS statusName')
            ->join('cmp.status', 'cs')
            ->join('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->join('cmp.customerUser', 'cu')
            ->join('cu.customer', 'c')
            ->join('c.billingAddresses', 'ba', Join::WITH, $queryBuilder->expr()->in('ba.id', $subQuery->getDQL()))
            ->groupBy('cmp.id')
            ->addGroupBy('o.companyName')
            ->addGroupBy('o.number')
            ->addGroupBy('o.id')
            ->addGroupBy('o.lastName')
            ->addGroupBy('o.firstName')
            ->addGroupBy('cu.id')
            ->addGroupBy('ba.id')
            ->setParameter('locale', $locale);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint|null
     */
    public function findById(int $id): ?Complaint
    {
        return $this->getComplaintRepository()->find($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getComplaintsByCustomerUserAndDomainIdAndLocale(
        CustomerUser $customerUser,
        int $domainId,
        string $locale,
    ): array {
        return $this->getComplaintRepository()->createQueryBuilder('c')
            ->addSelect('cdc, cdct, ci, cs, cst')
            ->leftJoin('c.deliveryCountry', 'cdc')
            ->leftJoin('cdc.translations', 'cdct', Join::WITH, 'cdct.locale = :locale')
            ->join('c.items', 'ci')
            ->join('c.status', 'cs')
            ->join('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->where('c.customerUser = :customerUser')
            ->andWhere('c.domainId = :domainId')
            ->setParameter('customerUser', $customerUser)
            ->setParameter('domainId', $domainId)
            ->setParameter('locale', $locale)
            ->getQuery()->getResult();
    }
}
