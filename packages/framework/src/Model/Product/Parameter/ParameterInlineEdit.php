<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\FormFactoryInterface;

class ParameterInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory $parameterGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface $parameterDataFactory
     */
    public function __construct(
        ParameterGridFactory $parameterGridFactory,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly ParameterDataFactoryInterface $parameterDataFactory,
    ) {
        parent::__construct($parameterGridFactory);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     * @return int
     */
    protected function createEntityAndGetId($parameterData)
    {
        $parameter = $this->parameterFacade->create($parameterData);

        return $parameter->getId();
    }

    /**
     * @param int $parameterId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function editEntity($parameterId, $parameterData)
    {
        $this->parameterFacade->edit($parameterId, $parameterData);
    }

    /**
     * @param int|null $parameterId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($parameterId)
    {
        if ($parameterId !== null) {
            $parameter = $this->parameterFacade->getById((int)$parameterId);
            $parameterData = $this->parameterDataFactory->createFromParameter($parameter);
        } else {
            $parameterData = $this->parameterDataFactory->create();
        }

        return $this->formFactory->create(ParameterFormType::class, $parameterData);
    }
}
