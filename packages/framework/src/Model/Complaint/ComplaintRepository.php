<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getComplaintsQueryBuilder(): QueryBuilder
    {
        return $this->getComplaintRepository()->createQueryBuilder('cmp')
            ->select('cmp')
            ->join('cmp.order', 'o')
            ->addSelect(
                '(CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName',
            );
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
