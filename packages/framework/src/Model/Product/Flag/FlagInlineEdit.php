<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Flag\FlagFormType;
use Symfony\Component\Form\FormFactoryInterface;

class FlagInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory $flagGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactoryInterface $flagDataFactory
     */
    public function __construct(
        FlagGridFactory $flagGridFactory,
        protected readonly FlagFacade $flagFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly FlagDataFactoryInterface $flagDataFactory,
    ) {
        parent::__construct($flagGridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @return int
     */
    protected function createEntityAndGetId($flagData)
    {
        $flag = $this->flagFacade->create($flagData);

        return $flag->getId();
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    protected function editEntity($flagId, $flagData)
    {
        $this->flagFacade->edit($flagId, $flagData);
    }

    /**
     * @param int|null $flagId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($flagId)
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
