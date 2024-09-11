<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\Status\Exception\ComplaintStatusNotFoundException;

class ComplaintStatusRepository
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
    protected function getComplaintStatusRepository(): EntityRepository
    {
        return $this->em->getRepository(ComplaintStatus::class);
    }

    /**
     * @param int $complaintStatusId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus|null
     */
    public function findById(int $complaintStatusId): ?ComplaintStatus
    {
        return $this->getComplaintStatusRepository()->find($complaintStatusId);
    }

    /**
     * @param int $complaintStatusId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function getById(int $complaintStatusId): ComplaintStatus
    {
        $complaintStatus = $this->findById($complaintStatusId);

        if ($complaintStatus === null) {
            $message = 'Complaint status with ID "' . $complaintStatusId . '" not found.';

            throw new ComplaintStatusNotFoundException($message);
        }

        return $complaintStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function getDefault(): ComplaintStatus
    {
        $complaintStatus = $this->getComplaintStatusRepository()->findOneBy(['statusType' => ComplaintStatusTypeEnum::STATUS_TYPE_NEW]);

        if ($complaintStatus === null) {
            $message = 'Default complaint status not found.';

            throw new ComplaintStatusNotFoundException($message);
        }

        return $complaintStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAll(): array
    {
        return $this->getComplaintStatusRepository()->findBy([], ['id' => 'asc']);
    }

    /**
     * @param int $complaintStatusId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAllExceptId(int $complaintStatusId): array
    {
        $qb = $this->getComplaintStatusRepository()->createQueryBuilder('cs')
            ->where('cs.id != :id')
            ->setParameter('id', $complaintStatusId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $oldComplaintStatus
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $newComplaintStatus
     */
    public function replaceComplaintStatus(
        ComplaintStatus $oldComplaintStatus,
        ComplaintStatus $newComplaintStatus,
    ): void {
        $this->em->createQueryBuilder()
            ->update(Complaint::class, 'cmp')
            ->set('cmp.status', ':newComplaintStatus')->setParameter('newComplaintStatus', $newComplaintStatus)
            ->where('cmp.status = :oldComplaintStatus')->setParameter('oldComplaintStatus', $oldComplaintStatus)
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     * @return bool
     */
    public function isComplaintStatusUsed(ComplaintStatus $complaintStatus): bool
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('c.id')
            ->from(Complaint::class, 'c')
            ->setMaxResults(1)
            ->where('c.status = :status')
            ->setParameter('status', $complaintStatus);

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR) !== null;
    }
}
