<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ComplaintStatusDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData
     */
    protected function createInstance(): ComplaintStatusData
    {
        return new ComplaintStatusData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData
     */
    public function create(): ComplaintStatusData
    {
        $complaintStatusData = $this->createInstance();
        $this->fillNew($complaintStatusData);

        return $complaintStatusData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    protected function fillNew(ComplaintStatusData $complaintStatusData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $complaintStatusData->name[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData
     */
    public function createFromComplaintStatus(ComplaintStatus $complaintStatus): ComplaintStatusData
    {
        $complaintStatusData = $this->createInstance();
        $this->fillFromComplaintStatus($complaintStatusData, $complaintStatus);

        return $complaintStatusData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $complaintStatus
     */
    protected function fillFromComplaintStatus(
        ComplaintStatusData $complaintStatusData,
        ComplaintStatus $complaintStatus,
    ): void {
        /** @var \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation[] $translations */
        $translations = $complaintStatus->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $complaintStatusData->name = $names;
    }
}
