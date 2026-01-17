<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGridInlineEdit implements GridInlineEditInterface
{
    public function __construct(
        protected readonly GridFactoryInterface $gridFactory,
        protected readonly AccessCheckerInterface $accessChecker,
    ) {
    }

    #[Override]
    public function saveForm(Request $request, int|string|null $rowId): int|string
    {
        if ($rowId !== null) {
            $this->accessChecker->denyUnlessCanEdit($this->getRoleConstant());
        } else {
            $this->accessChecker->denyUnlessCanCreate($this->getRoleConstant());
        }

        $form = $this->getForm($rowId);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $formErrors = [];

            /** @var \Symfony\Component\Form\FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $formErrors[] = $error->getMessage();
            }

            throw new InvalidFormDataException($formErrors);
        }

        $formData = $form->getData();

        if ($rowId !== null) {
            $this->editEntity($rowId, $formData);
        } else {
            $rowId = $this->createEntityAndGetId($formData);
        }

        return $rowId;
    }

    #[Override]
    public function getGrid(): Grid
    {
        $grid = $this->gridFactory->create($this->getRoleConstant());
        $grid->setInlineEditService($this);

        return $grid;
    }

    #[Override]
    public function canAddNewRow(): bool
    {
        return $this->accessChecker->canCreate($this->getRoleConstant());
    }

    /**
     * Since Symfony 3.4, the best practice for service names is using FQCN
     * if you don't follow this best practice you should override this method
     */
    #[Override]
    public function getServiceName(): string
    {
        return static::class;
    }

    #[Override]
    abstract public function getForm(int|string|null $rowId): FormInterface;

    abstract protected function editEntity(int|string $rowId, mixed $formData): void;

    abstract protected function createEntityAndGetId(mixed $formData): int|string;

    abstract protected function getRoleConstant(): string;
}
