<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGridInlineEdit implements GridInlineEditInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface
     */
    private $gridFactory;

    public function __construct(GridFactoryInterface $gridFactory)
    {
        $this->gridFactory = $gridFactory;
    }

    /**
     * @param int|string|null $rowId
     * @return int|string
     */
    public function saveForm(Request $request, $rowId)
    {
        $form = $this->getForm($rowId);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $formErrors = [];
            foreach ($form->getErrors(true) as $error) {
                /* @var $error \Symfony\Component\Form\FormError */
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

    public function getGrid(): \Shopsys\FrameworkBundle\Component\Grid\Grid
    {
        $grid = $this->gridFactory->create();
        $grid->setInlineEditService($this);

        return $grid;
    }

    public function canAddNewRow(): bool
    {
        return true;
    }

    /**
     * Since Symfony 3.4, the best practice for service names is using FQCN
     * if you don't follow this best practice you should override this method
     */
    public function getServiceName(): string
    {
        return static::class;
    }

    /**
     * @param int|string|null $rowId
     */
    abstract public function getForm($rowId): \Symfony\Component\Form\FormInterface;

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
