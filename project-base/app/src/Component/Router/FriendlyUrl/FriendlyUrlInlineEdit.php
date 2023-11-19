<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Form\Admin\FriendlyUrlFormType;
use LogicException;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
 */
class FriendlyUrlInlineEdit extends AbstractGridInlineEdit
{
    private QuickSearchFormData $gridQuickSearchFormData;

    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlGridFactory $gridFactory
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlDataFactory $friendlyUrlDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        FriendlyUrlGridFactory $gridFactory,
        private FormFactoryInterface $formFactory,
        private FriendlyUrlFacade $friendlyUrlFacade,
        private FriendlyUrlDataFactory $friendlyUrlDataFactory,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct($gridFactory);

        $this->gridQuickSearchFormData = new QuickSearchFormData();
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($rowId): \Symfony\Component\Form\FormInterface
    {
        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($this->adminDomainTabsFacade->getSelectedDomainId(), $rowId);
        $friendlyUrlData = $this->friendlyUrlDataFactory->createFromFriendlyUrl($friendlyUrl);

        return $this->formFactory->create(FriendlyUrlFormType::class, $friendlyUrlData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid(): \Shopsys\FrameworkBundle\Component\Grid\Grid
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
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlData $formData
     */
    protected function editEntity($rowId, $formData): void
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
    protected function createEntityAndGetId($formData): int|string
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
