<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractGridInlineEdit implements GridInlineEditInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface $gridFactory
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     */
    public function __construct(
        protected readonly GridFactoryInterface $gridFactory,
        protected readonly Security $security,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string|null $rowId
     * @return int|string
     */
    public function saveForm(Request $request, int|string|null $rowId): int|string
    {
        $this->checkAdministratorHasEditRole();

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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function getGrid(): Grid
    {
        $grid = $this->gridFactory->create($this->getEditRole());

        if ($this->canEdit()) {
            $grid->setInlineEditService($this);
        }

        return $grid;
    }

    /**
     * @return bool
     */
    public function canAddNewRow(): bool
    {
        return $this->canEdit();
    }

    /**
     * Since Symfony 3.4, the best practice for service names is using FQCN
     * if you don't follow this best practice you should override this method
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return static::class;
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    abstract public function getForm(int|string|null $rowId): FormInterface;

    /**
     * @param int|string $rowId
     * @param mixed $formData
     */
    abstract protected function editEntity(int|string $rowId, mixed $formData): void;

    /**
     * @param mixed $formData
     * @return int|string
     */
    abstract protected function createEntityAndGetId(mixed $formData): int|string;

    /**
     * @return string
     */
    abstract protected function getEditRole(): string;

    protected function checkAdministratorHasEditRole(): void
    {
        if (!$this->canEdit()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return bool
     */
    protected function canEdit(): bool
    {
        return $this->security->isGranted($this->getEditRole());
    }
}
