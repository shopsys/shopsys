<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;

class ComplaintStatusDataFixture extends AbstractReferenceFixture
{
    public const string COMPLAINT_STATUS_NEW = 'complaint_status_new';
    public const string COMPLAINT_STATUS_IN_PROGRESS = 'complaint_status_in_progress';
    public const string COMPLAINT_STATUS_RESOLVED = 'complaint_status_resolved';

    public function __construct(
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createComplaintStatusReference(1, self::COMPLAINT_STATUS_NEW);
        $this->createComplaintStatusReference(2, self::COMPLAINT_STATUS_RESOLVED);
        $this->createComplaintStatusReference(3, self::COMPLAINT_STATUS_IN_PROGRESS);
    }

    /**
     * Complaint statuses are created (with specific ids) in database migration.
     *
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
