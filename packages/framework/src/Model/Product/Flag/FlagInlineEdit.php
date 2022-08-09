<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Flag\FlagFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FlagInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected $flagFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface
     */
    protected $flagDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory $flagGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface $flagDataFactory
     */
    public function __construct(
        FlagGridFactory $flagGridFactory,
        FlagFacade $flagFacade,
        FormFactoryInterface $formFactory,
        FlagDataFactoryInterface $flagDataFactory
    ) {
        parent::__construct($flagGridFactory);

        $this->flagFacade = $flagFacade;
        $this->formFactory = $formFactory;
        $this->flagDataFactory = $flagDataFactory;
    }

    /**
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $flag = $this->flagFacade->create($formData);

        return $flag->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->flagFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $flagId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($flagId): FormInterface
    {
        if ($flagId !== null) {
            $flag = $this->flagFacade->getById((int)$flagId);
            $flagData = $this->flagDataFactory->createFromFlag($flag);
        } else {
            $flagData = $this->flagDataFactory->create();
        }

        return $this->formFactory->create(FlagFormType::class, $flagData);
    }
}
