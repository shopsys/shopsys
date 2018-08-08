<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\FormFactoryInterface;

class ParameterInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    private $parameterFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface
     */
    private $parameterDataFactory;

    public function __construct(
        ParameterGridFactory $parameterGridFactory,
        ParameterFacade $parameterFacade,
        FormFactoryInterface $formFactory,
        ParameterDataFactoryInterface $parameterDataFactory
    ) {
        parent::__construct($parameterGridFactory);
        $this->parameterFacade = $parameterFacade;
        $this->formFactory = $formFactory;
        $this->parameterDataFactory = $parameterDataFactory;
    }
    protected function createEntityAndGetId(\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData): int
    {
        $parameter = $this->parameterFacade->create($parameterData);

        return $parameter->getId();
    }
    
    protected function editEntity(int $parameterId, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData): void
    {
        $this->parameterFacade->edit($parameterId, $parameterData);
    }

    /**
     * @param int|null $parameterId
     */
    public function getForm(?int $parameterId): \Symfony\Component\Form\FormInterface
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
