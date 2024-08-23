<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class ComplaintApiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory $complaintFactory
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory $complaintItemFactory
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintRepository $complaintRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ComplaintFactory $complaintFactory,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly ComplaintItemFactory $complaintItemFactory,
        protected readonly ComplaintRepository $complaintRepository,
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
                UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
                $complaint->getCustomerUser(),
            );
        }

        return $complaint;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @param string|null $search
     * @return array
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        ?string $search = null,
    ): array {
        return $this->complaintRepository->getCustomerUserComplaintsLimitedList($customerUser, $limit, $offset, $search);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $search
     * @return int
     */
    public function getCustomerUserComplaintsLimitedListCount(
        CustomerUser $customerUser,
        ?string $search = null,
    ): int {
        return $this->complaintRepository->getCustomerUserComplaintsListCount($customerUser, $search);
    }

    /**
     * @param string $complaintNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint|null
     */
    public function findByComplaintNumberAndCustomerUser(
        string $complaintNumber,
        CustomerUser $customerUser,
    ): ?Complaint {
        return $this->complaintRepository->findByComplaintNumberAndCustomerUser($complaintNumber, $customerUser);
    }
}
