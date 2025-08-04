<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use LogicException;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\FriendlyUrl\FriendlyUrlFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @property \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
 */
class FriendlyUrlInlineEdit extends AbstractGridInlineEdit
{
    protected QuickSearchFormData $gridQuickSearchFormData;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface $accessChecker
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory $friendlyUrlDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        FriendlyUrlGridFactory $gridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly FriendlyUrlDataFactory $friendlyUrlDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct($gridFactory, $accessChecker);

        $this->gridQuickSearchFormData = new QuickSearchFormData();
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($this->adminDomainTabsFacade->getSelectedDomainId(), $rowId);
        $friendlyUrlData = $this->friendlyUrlDataFactory->createFromFriendlyUrl($friendlyUrl);

        return $this->formFactory->create(FriendlyUrlFormType::class, $friendlyUrlData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    #[Override]
    public function getGrid(): Grid
    {
        $this->gridFactory->setQuickSearchFormData($this->getGridQuickSearchFormData());
        $grid = $this->gridFactory->create($this->getRoleConstant());

        $grid->setInlineEditService($this);

        return $grid;
    }

    /**
     * @return bool
     */
    #[Override]
    public function canAddNewRow(): bool
    {
        return false;
    }

    /**
     * @param int|string $rowId
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData $formData
     */
    #[Override]
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->friendlyUrlFacade->setRedirect(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $rowId,
            $formData,
        );
    }

    /**
     * @param mixed $formData
     */
    #[Override]
    protected function createEntityAndGetId(mixed $formData): never
    {
        throw new LogicException('Creating a new unused friendly URL is not supported.');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData
     */
    public function getGridQuickSearchFormData(): QuickSearchFormData
    {
        return $this->gridQuickSearchFormData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $gridQuickSearchFormData
     */
    public function setGridQuickSearchFormData(QuickSearchFormData $gridQuickSearchFormData): void
    {
        $this->gridQuickSearchFormData = $gridQuickSearchFormData;
    }

    /**
     * @return string
     */
    #[Override]
    protected function getRoleConstant(): string
    {
        return AdminRoleConstant::ROLE_FRIENDLY_URL;
    }
}
