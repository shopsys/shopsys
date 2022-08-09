<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Group\Grid;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Group\PricingGroupFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class PricingGroupInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    protected $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface
     */
    protected $pricingGroupDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\Grid\PricingGroupGridFactory $pricingGroupGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface $pricingGroupDataFactory
     */
    public function __construct(
        PricingGroupGridFactory $pricingGroupGridFactory,
        PricingGroupFacade $pricingGroupFacade,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        FormFactoryInterface $formFactory,
        PricingGroupDataFactoryInterface $pricingGroupDataFactory
    ) {
        parent::__construct($pricingGroupGridFactory);

        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->formFactory = $formFactory;
        $this->pricingGroupDataFactory = $pricingGroupDataFactory;
    }

    /**
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $pricingGroup = $this->pricingGroupFacade->create(
            $formData,
            $this->adminDomainTabsFacade->getSelectedDomainId()
        );

        return $pricingGroup->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->pricingGroupFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $pricingGroupId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($pricingGroupId): FormInterface
    {
        if ($pricingGroupId !== null) {
            $pricingGroupId = (int)$pricingGroupId;
            $pricingGroup = $this->pricingGroupFacade->getById($pricingGroupId);
            $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($pricingGroup);
        } else {
            $pricingGroupData = $this->pricingGroupDataFactory->create();
        }

        return $this->formFactory->create(PricingGroupFormType::class, $pricingGroupData);
    }
}
