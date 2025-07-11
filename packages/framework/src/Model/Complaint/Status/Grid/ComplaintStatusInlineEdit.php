<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Form\Admin\Complaint\Status\ComplaintStatusFormType;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ComplaintStatusInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\Grid\ComplaintStatusGridFactory $complaintStatusGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface $accessChecker
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusDataFactory $complaintStatusDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ComplaintStatusGridFactory $complaintStatusGridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly ComplaintStatusDataFactory $complaintStatusDataFactory,
        protected readonly Domain $domain,
    ) {
        parent::__construct($complaintStatusGridFactory, $accessChecker);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @return int
     */
    #[Override]
    protected function createEntityAndGetId(mixed $complaintStatusData): int
    {
        if (!$this->domain->hasAdminAllDomainsEnabled()) {
            throw new InvalidFormDataException([
                t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'),
            ]);
        }

        $complaintStatus = $this->complaintStatusFacade->create($complaintStatusData);

        return $complaintStatus->getId();
    }

    /**
     * @param int|string $complaintStatusId
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    #[Override]
    protected function editEntity(int|string $complaintStatusId, mixed $complaintStatusData): void
    {
        $this->complaintStatusFacade->edit($complaintStatusId, $complaintStatusData);
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $complaintStatus = $this->complaintStatusFacade->getById((int)$rowId);
            $complaintStatusData = $this->complaintStatusDataFactory->createFromComplaintStatus($complaintStatus);
        } else {
            $complaintStatusData = $this->complaintStatusDataFactory->create();
        }

        return $this->formFactory->create(ComplaintStatusFormType::class, $complaintStatusData);
    }

    /**
     * @return string
     */
    #[Override]
    protected function getRoleConstant(): string
    {
        return 'ROLE_COMPLAINT';
    }
}
