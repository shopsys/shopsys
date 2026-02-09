<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMail;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;

class ComplaintStatusFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ComplaintStatusFactory $complaintStatusFactory,
        protected readonly ComplaintStatusRepository $complaintStatusRepository,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ComplaintMail $complaintMail,
    ) {
    }

    public function create(ComplaintStatusData $complaintStatusData): ComplaintStatus
    {
        $complaintStatus = $this->complaintStatusFactory->create(
            $complaintStatusData,
            ComplaintStatusTypeEnum::STATUS_TYPE_IN_PROGRESS,
        );
        $this->em->persist($complaintStatus);
        $this->em->flush();

        $this->mailTemplateFacade->createMailTemplateForAllDomains(
            $this->complaintMail->getMailTemplateNameByStatus($complaintStatus),
            null,
            $complaintStatus,
        );

        return $complaintStatus;
    }

    public function edit(int $complaintStatusId, ComplaintStatusData $complaintStatusData): ComplaintStatus
    {
        $complaintStatus = $this->complaintStatusRepository->getById($complaintStatusId);
        $complaintStatus->edit($complaintStatusData);
        $this->em->flush();

        return $complaintStatus;
    }

    public function deleteById(int $complaintStatusId, ?int $newComplaintStatusId = null): void
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

    public function getById(int $complaintStatusId): ComplaintStatus
    {
        return $this->complaintStatusRepository->getById($complaintStatusId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]
     */
    public function getAllExceptId(int $complaintStatusId): array
    {
        return $this->complaintStatusRepository->getAllExceptId($complaintStatusId);
    }

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

    public function getDefault(): ComplaintStatus
    {
        return $this->complaintStatusRepository->getDefault();
    }
}
