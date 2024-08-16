<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Complaint\Exception\ComplaintNotFoundException;

class ComplaintFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository $complaintRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getComplaintsQueryBuilder(): QueryBuilder
    {
        return $this->complaintRepository->getComplaintsQueryBuilder();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function getById(int $id): Complaint
    {
        $complaint = $this->complaintRepository->findById($id);

        if ($complaint === null) {
            throw new ComplaintNotFoundException('Complaint with ID "' . $id . '" not found.');
        }

        return $complaint;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     */
    public function edit(int $id, ComplaintData $complaintData): void
    {
        $complaint = $this->getById($id);
        $complaint->edit($complaintData);
        $this->em->flush();
    }
}
