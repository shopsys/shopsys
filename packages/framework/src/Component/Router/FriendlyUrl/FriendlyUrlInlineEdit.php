<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use LogicException;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\FriendlyUrl\FriendlyUrlFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @property \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
 */
class FriendlyUrlInlineEdit extends AbstractGridInlineEdit
{
    protected QuickSearchFormData $gridQuickSearchFormData;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataFactory $friendlyUrlDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        FriendlyUrlGridFactory $gridFactory,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly FriendlyUrlDataFactory $friendlyUrlDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct($gridFactory);

        $this->gridQuickSearchFormData = new QuickSearchFormData();
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($rowId)
    {
        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($this->adminDomainTabsFacade->getSelectedDomainId(), $rowId);
        $friendlyUrlData = $this->friendlyUrlDataFactory->createFromFriendlyUrl($friendlyUrl);

        return $this->formFactory->create(FriendlyUrlFormType::class, $friendlyUrlData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid()
    {
        $this->gridFactory->setQuickSearchFormData($this->getGridQuickSearchFormData());
        $grid = $this->gridFactory->create();
        $grid->setInlineEditService($this);

        return $grid;
    }

    /**
     * @return bool
     */
    public function canAddNewRow(): bool
    {
        return false;
    }

    /**
     * @param string $rowId
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData $formData
     */
    protected function editEntity($rowId, $formData)
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
    protected function createEntityAndGetId($formData): never
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
}
