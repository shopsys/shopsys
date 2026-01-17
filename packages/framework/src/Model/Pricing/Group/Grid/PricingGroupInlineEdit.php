<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Group\PricingGroupFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class PricingGroupInlineEdit extends AbstractGridInlineEdit
{
    public function __construct(
        PricingGroupGridFactory $pricingGroupGridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly PricingGroupDataFactory $pricingGroupDataFactory,
    ) {
        parent::__construct($pricingGroupGridFactory, $accessChecker);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     */
    #[Override]
    protected function createEntityAndGetId(mixed $pricingGroupData): int|string
    {
        $pricingGroup = $this->pricingGroupFacade->create(
            $pricingGroupData,
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $pricingGroup->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     */
    #[Override]
    protected function editEntity(int|string $pricingGroupId, mixed $pricingGroupData): void
    {
        $this->pricingGroupFacade->edit($pricingGroupId, $pricingGroupData);
    }

    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $rowId = (int)$rowId;
            $pricingGroup = $this->pricingGroupFacade->getById($rowId);
            $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($pricingGroup);
        } else {
            $pricingGroupData = $this->pricingGroupDataFactory->create();
        }

        return $this->formFactory->create(PricingGroupFormType::class, $pricingGroupData);
    }

    #[Override]
    protected function getRoleConstant(): string
    {
        return AdminRoleConstant::ROLE_PRICING_GROUP;
    }
}
