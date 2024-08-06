<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;

class ComplaintApiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory $complaintFactory
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory $complaintItemFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ComplaintFactory $complaintFactory,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly ComplaintItemFactory $complaintItemFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function create(ComplaintData $complaintData): Complaint
    {
        $complaintItemsData = [];
        $complaintItems = [];

        foreach ($complaintData->complaintItems as $key => $complaintItem) {
            $complaintItemsData[$key] = $complaintItem;
            $complaintItems[$key] = $this->complaintItemFactory->create($complaintItem);
        }

        $complaint = $this->complaintFactory->create($complaintData, $complaintItems);

        $this->em->persist($complaint);
        $this->em->flush();

        foreach ($complaintItems as $key => $item) {
            $this->customerUploadedFileFacade->manageFiles(
                $item,
                $complaintItemsData[$key]->files,
                CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
                $complaint->getCustomerUser(),
            );
        }

        return $complaint;
    }
}
