<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMail;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ComplaintStatusFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFactory $complaintStatusFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusRepository $complaintStatusRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ComplaintStatusFactory $complaintStatusFactory,
        protected readonly ComplaintStatusRepository $complaintStatusRepository,
        protected readonly MailTemplateFacade $mailTemplateFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function create(ComplaintStatusData $complaintStatusData): ComplaintStatus
    {
        $complaintStatus = $this->complaintStatusFactory->create(
            $complaintStatusData,
            ComplaintStatusTypeEnum::STATUS_TYPE_IN_PROGRESS,
        );
        $this->em->persist($complaintStatus);
        $this->em->flush();

        $this->mailTemplateFacade->createMailTemplateForAllDomains(
            ComplaintMail::getMailTemplateNameByStatus($complaintStatus),
        );

        return $complaintStatus;
    }

    /**
     * @param int $complaintStatusId
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function edit(int $complaintStatusId, ComplaintStatusData $complaintStatusData): ComplaintStatus
    {
        $complaintStatus = $this->complaintStatusRepository->getById($complaintStatusId);
        $complaintStatus->edit($complaintStatusData);
        $this->em->flush();

        return $complaintStatus;
    }

    /**
     * @param int $complaintStatusId
     * @param int|null $newComplaintStatusId
     */
    public function deleteById(int $complaintStatusId, int $newComplaintStatusId = null): void
    {
        $complaintStatus = $this->complaintStatusRepository->getById($complaintStatusId);
        $complaintStatus->checkForDelete();

        if ($newComplaintStatusId !== null) {
            $newComplaintStatus = $this->complaintStatusRepository->getById($newComplaintStatusId);
            $this->complaintStatusRepository->replaceComplaintStatus($complaintStatus, $newComplaintStatus);
        }

        $this->em->remove($complaintStatus);
        $this->em->flush();
    }

    /**
     * @param int $complaintStatusId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function getById(int $complaintStatusId): ComplaintStatus
    {
        return $this->complaintStatusRepository->getById($complaintStatusId);
    }

    /**
     * @param int $complaintStatusId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAllExceptId(int $complaintStatusId): array
    {
        return $this->complaintStatusRepository->getAllExceptId($complaintStatusId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     * @return bool
     */
    public function isComplaintStatusUsed(ComplaintStatus $complaintStatus): bool
    {
        return $this->complaintStatusRepository->isComplaintStatusUsed($complaintStatus);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAll(): array
    {
        return $this->complaintStatusRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus
     */
    public function getDefault(): ComplaintStatus
    {
        return $this->complaintStatusRepository->getDefault();
    }
}
