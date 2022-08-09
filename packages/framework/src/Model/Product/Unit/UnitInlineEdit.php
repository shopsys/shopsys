<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Unit\UnitFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UnitInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    protected $unitFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface
     */
    protected $unitDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitGridFactory $unitGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitDataFactoryInterface $unitDataFactory
     */
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

    /**
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $unit = $this->unitFacade->create($formData);

        return $unit->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->unitFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $unitId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($unitId): FormInterface
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
