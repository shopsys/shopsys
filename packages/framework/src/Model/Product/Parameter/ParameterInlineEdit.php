<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ParameterInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    protected $parameterFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface
     */
    protected $parameterDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory $parameterGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface $parameterDataFactory
     */
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

    /**
     * @param mixed $formData
     * @return int
     */
    protected function createEntityAndGetId(mixed $formData): int
    {
        $parameter = $this->parameterFacade->create($formData);

        return $parameter->getId();
    }

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    protected function editEntity(int|string $rowId, mixed $formData): void
    {
        $this->parameterFacade->edit($rowId, $formData);
    }

    /**
     * @param int|null $parameterId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($parameterId): FormInterface
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
