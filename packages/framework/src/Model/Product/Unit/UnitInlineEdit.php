<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Unit\UnitFormType;
use Symfony\Component\Form\FormFactoryInterface;

class UnitInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    private $unitFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface
     */
    private $unitDataFactory;

    public function __construct(
        UnitGridFactory $unitGridFactory,
        UnitFacade $unitFacade,
        FormFactoryInterface $formFactory,
        UnitDataFactoryInterface $unitDataFactory
    ) {
        parent::__construct($unitGridFactory);
        $this->unitFacade = $unitFacade;
        $this->formFactory = $formFactory;
        $this->unitDataFactory = $unitDataFactory;
    }
    
    protected function createEntityAndGetId(\Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData): int
    {
        $unit = $this->unitFacade->create($unitData);

        return $unit->getId();
    }
    
    protected function editEntity(int $unitId, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData): void
    {
        $this->unitFacade->edit($unitId, $unitData);
    }

    /**
     * @param int|null $unitId
     */
    public function getForm(?int $unitId): \Symfony\Component\Form\FormInterface
    {
        if ($unitId !== null) {
            $unit = $this->unitFacade->getById((int)$unitId);
            $unitData = $this->unitDataFactory->createFromUnit($unit);
        } else {
            $unitData = $this->unitDataFactory->create();
        }

        return $this->formFactory->create(UnitFormType::class, $unitData);
    }
}
