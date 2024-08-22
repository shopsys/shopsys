<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Exception\ComplaintNotFoundException;

class ComplaintFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository $complaintRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     */
    public function __construct(
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
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
        $complaint->edit($complaintData);

        $this->em->flush();

        foreach ($complaintData->complaintItems as $complaintItemId => $complaintItemData) {
            $this->customerUploadedFileFacade->manageFiles($this->getComplaintItemById($complaintItemId, $complaint->getItems()), $complaintItemData->files);
        }
    }

    /**
     * @param int $complaintItemId
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem[] $complaintItems
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem
     */
    protected function getComplaintItemById(int $complaintItemId, array $complaintItems): ComplaintItem
    {
        foreach ($complaintItems as $complaintItem) {
            if ($complaintItem->getId() === $complaintItemId) {
                return $complaintItem;
            }
        }

        throw new LogicException('Complaint item with ID "' . $complaintItemId . '" not found.');
    }
}
