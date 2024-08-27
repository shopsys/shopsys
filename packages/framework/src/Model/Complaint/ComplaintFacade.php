<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Exception\ComplaintNotFoundException;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMailFacade;

class ComplaintFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository $complaintRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMailFacade $complaintMailFacade
     */
    public function __construct(
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly ComplaintMailFacade $complaintMailFacade,
    ) {
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
        $statusBefore = $complaint->getStatus();

        $complaint->edit($complaintData);
        $this->editItems($complaint, $complaintData->complaintItems);
        $this->em->flush();

        if ($complaint->getStatus() !== $statusBefore) {
            $this->complaintMailFacade->sendEmail($complaint);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[] $complaintItemsData
     */
    protected function editItems(Complaint $complaint, array $complaintItemsData): void
    {
        foreach ($complaint->getItems() as $complaintItem) {
            $complaintItemId = $complaintItem->getId();

            if (!array_key_exists($complaintItemId, $complaintItemsData)) {
                continue;
            }

            $orderItemData = $complaintItemsData[$complaintItemId];
            $complaintItem->edit($orderItemData);
        }

        $this->em->flush();
    }
}
