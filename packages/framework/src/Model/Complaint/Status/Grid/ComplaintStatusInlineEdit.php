<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Grid;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Complaint\Status\ComplaintStatusFormType;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ComplaintStatusInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\Grid\ComplaintStatusGridFactory $complaintStatusGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusDataFactory $complaintStatusDataFactory
     */
    public function __construct(
        ComplaintStatusGridFactory $complaintStatusGridFactory,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly ComplaintStatusDataFactory $complaintStatusDataFactory,
    ) {
        parent::__construct($complaintStatusGridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @return int
     */
    protected function createEntityAndGetId($complaintStatusData): int
    {
        $complaintStatus = $this->complaintStatusFacade->create($complaintStatusData);

        return $complaintStatus->getId();
    }

    /**
     * @param int $complaintStatusId
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    protected function editEntity($complaintStatusId, $complaintStatusData): void
    {
        $this->complaintStatusFacade->edit($complaintStatusId, $complaintStatusData);
    }

    /**
     * @param int|null $complaintStatusId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($complaintStatusId): FormInterface
    {
        if ($complaintStatusId !== null) {
            $complaintStatus = $this->complaintStatusFacade->getById((int)$complaintStatusId);
            $complaintStatusData = $this->complaintStatusDataFactory->createFromComplaintStatus($complaintStatus);
        } else {
            $complaintStatusData = $this->complaintStatusDataFactory->create();
        }

        return $this->formFactory->create(ComplaintStatusFormType::class, $complaintStatusData);
    }
}
