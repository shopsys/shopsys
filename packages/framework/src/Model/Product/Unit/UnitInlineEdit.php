<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Unit\UnitFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UnitInlineEdit extends AbstractGridInlineEdit
{
    public function __construct(
        UnitGridFactory $unitGridFactory,
        AccessCheckerInterface $accessChecker,
        protected readonly UnitFacade $unitFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly UnitDataFactory $unitDataFactory,
        protected readonly Domain $domain,
    ) {
        parent::__construct($unitGridFactory, $accessChecker);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     */
    #[Override]
    protected function createEntityAndGetId(mixed $unitData): int|string
    {
        if (!$this->domain->hasAdminAllDomainsEnabled()) {
            throw new InvalidFormDataException([
                t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'),
            ]);
        }

        $unit = $this->unitFacade->create($unitData);

        return $unit->getId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     */
    #[Override]
    protected function editEntity(int|string $unitId, mixed $unitData): void
    {
        $this->unitFacade->edit($unitId, $unitData);
    }

    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $unit = $this->unitFacade->getById((int)$rowId);
            $unitData = $this->unitDataFactory->createFromUnit($unit);
        } else {
            $unitData = $this->unitDataFactory->create();
        }

        return $this->formFactory->create(UnitFormType::class, $unitData);
    }

    #[Override]
    protected function getRoleConstant(): string
    {
        return AdminRoleConstant::ROLE_UNIT;
    }
}
