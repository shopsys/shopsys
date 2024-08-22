<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

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
        return $this->getComplaintRepository()->createQueryBuilder('cmp')
            ->select('cmp')
            ->addSelect('o.number as orderNumber')
            ->addSelect('o.id as orderId')
            ->join('cmp.order', 'o')
            ->addSelect(
                '(CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName',
            )
            ->addSelect('MAX(cst.name) AS statusName')
            ->join('cmp.status', 'cs')
            ->join('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->groupBy('cmp.id')
            ->addGroupBy('o.companyName')
            ->addGroupBy('o.number')
            ->addGroupBy('o.id')
            ->addGroupBy('o.lastName')
            ->addGroupBy('o.firstName')
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
}
