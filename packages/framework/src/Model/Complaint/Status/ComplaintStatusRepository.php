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
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getComplaintStatusRepository(): EntityRepository
    {
        return $this->em->getRepository(ComplaintStatus::class);
    }

    public function findById(int $complaintStatusId): ?ComplaintStatus
    {
        return $this->getComplaintStatusRepository()->find($complaintStatusId);
    }

    public function getById(int $complaintStatusId): ComplaintStatus
    {
        $complaintStatus = $this->findById($complaintStatusId);

        if ($complaintStatus === null) {
            $message = 'Complaint status with ID "' . $complaintStatusId . '" not found.';

            throw new ComplaintStatusNotFoundException($message);
        }

        return $complaintStatus;
    }

    public function findByCode(string $code): ?ComplaintStatus
    {
        return $this->getComplaintStatusRepository()->findOneBy(['code' => $code]);
    }

    /**
     * @param string[] $codes
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAllByCodes(array $codes): array
    {
        return $this->getComplaintStatusRepository()->findBy(['code' => $codes]);
    }

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
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAllExceptId(int $complaintStatusId): array
    {
        $qb = $this->getComplaintStatusRepository()->createQueryBuilder('cs')
            ->where('cs.id != :id')
            ->setParameter('id', $complaintStatusId);

        return $qb->getQuery()->getResult();
    }

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
