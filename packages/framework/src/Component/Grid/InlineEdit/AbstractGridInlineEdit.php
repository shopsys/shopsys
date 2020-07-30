<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGridInlineEdit implements GridInlineEditInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface
     */
    protected $gridFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface $gridFactory
     */
    public function __construct(GridFactoryInterface $gridFactory)
    {
        $this->gridFactory = $gridFactory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string|null $rowId
     * @return int|string
     */
    public function saveForm(Request $request, $rowId)
    {
        $form = $this->getForm($rowId);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $formErrors = [];

            /** @var \Symfony\Component\Form\FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $formErrors[] = $error->getMessage();
            }
            throw new \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException($formErrors);
        }

        $formData = $form->getData();
        if ($rowId !== null) {
            $this->editEntity($rowId, $formData);
        } else {
            $rowId = $this->createEntityAndGetId($formData);
        }

        return $rowId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid()
    {
        $grid = $this->gridFactory->create();
        $grid->setInlineEditService($this);

        return $grid;
    }

    /**
     * @return bool
     */
    public function canAddNewRow()
    {
        return true;
    }

    /**
     * Since Symfony 3.4, the best practice for service names is using FQCN
     * if you don't follow this best practice you should override this method
     *
     * @return string
     */
    public function getServiceName()
    {
        return static::class;
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    abstract public function getForm($rowId);

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    abstract protected function editEntity($rowId, $formData);

    /**
     * @param mixed $formData
     * @return int|string
     */
    abstract protected function createEntityAndGetId($formData);
}
