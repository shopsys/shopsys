<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Migrations\Version20261119110000;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;

class ComplaintStatusDataFixture extends AbstractReferenceFixture
{
    public const string COMPLAINT_STATUS_NEW = 'complaint_status_new';
    public const string COMPLAINT_STATUS_IN_PROGRESS = 'complaint_status_in_progress';
    public const string COMPLAINT_STATUS_RESOLVED = 'complaint_status_resolved';
    public const string COMPLAINT_STATUS_RESOLVED_CLAIMABLE_AGAIN = 'complaint_status_resolved_claimable_again';

    public function __construct(
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly ComplaintStatusDataFactory $complaintStatusDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->createComplaintStatusReference(1, self::COMPLAINT_STATUS_NEW);
        $this->createComplaintStatusReference(2, self::COMPLAINT_STATUS_RESOLVED);
        $this->createComplaintStatusReference(3, self::COMPLAINT_STATUS_IN_PROGRESS);
        $this->createRejectedComplaintStatusReference();
    }

    /**
     * The status is created in database migration, it is created here only if the migration was skipped.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20261119110000
     */
    private function createRejectedComplaintStatusReference(): void
    {
        $rejectedComplaintStatus = $this->complaintStatusFacade->findByCode(Version20261119110000::REJECTED_COMPLAINT_STATUS_CODE)
            ?? $this->createRejectedComplaintStatus();

        $this->createComplaintStatusReference(
            $rejectedComplaintStatus->getId(),
            self::COMPLAINT_STATUS_RESOLVED_CLAIMABLE_AGAIN,
        );
    }

    private function createRejectedComplaintStatus(): ComplaintStatus
    {
        $complaintStatusData = $this->complaintStatusDataFactory->create();

        foreach (array_keys($complaintStatusData->name) as $locale) {
            $complaintStatusData->name[$locale] = t('Rejected', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
        }
        $complaintStatusData->statusType = ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN;

        return $this->complaintStatusFacade->create($complaintStatusData);
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
