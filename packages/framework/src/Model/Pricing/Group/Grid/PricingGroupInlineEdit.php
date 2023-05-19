<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group\Grid;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Group\PricingGroupFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\FormFactoryInterface;

class PricingGroupInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\Grid\PricingGroupGridFactory $pricingGroupGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface $pricingGroupDataFactory
     */
    public function __construct(
        PricingGroupGridFactory $pricingGroupGridFactory,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly PricingGroupDataFactoryInterface $pricingGroupDataFactory,
    ) {
        parent::__construct($pricingGroupGridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @return int
     */
    protected function createEntityAndGetId($pricingGroupData)
    {
        $pricingGroup = $this->pricingGroupFacade->create(
            $pricingGroupData,
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $pricingGroup->getId();
    }

    /**
     * @param int $pricingGroupId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     */
    protected function editEntity($pricingGroupId, $pricingGroupData)
    {
        $this->pricingGroupFacade->edit($pricingGroupId, $pricingGroupData);
    }

    /**
     * @param int|null $pricingGroupId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($pricingGroupId)
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
