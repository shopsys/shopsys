<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;

class ComplaintStatusDataFixture extends AbstractReferenceFixture
{
    public const string COMPLAINT_STATUS_NEW = 'complaint_status_new';
    public const string COMPLAINT_STATUS_RESOLVED = 'complaint_status_resolved';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     */
    public function __construct(
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createComplaintStatusReference(1, self::COMPLAINT_STATUS_NEW);
        $this->createComplaintStatusReference(2, self::COMPLAINT_STATUS_RESOLVED);
    }

    /**
     * Complaint statuses are created (with specific ids) in database migration.
     *
     * @param int $complaintStatusId
     * @param string $referenceName
     * @see \Shopsys\FrameworkBundle\Migrations\Version20240816221930
     */
    private function createComplaintStatusReference(
        int $complaintStatusId,
        string $referenceName,
    ): void {
        $complaintStatus = $this->complaintStatusFacade->getById($complaintStatusId);
        $this->addReference($referenceName, $complaintStatus);
    }
}
