<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

readonly class OrderDetailSection
{
    public function __construct(
        protected string $id,
        protected string $viewTemplate,
        protected string $formTemplate,
        protected string $modalTitle,
        protected string $successMessage,
        protected string $modalDialogClass = 'modal-xl',
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getViewTemplate(): string
    {
        return $this->viewTemplate;
    }

    public function getFormTemplate(): string
    {
        return $this->formTemplate;
    }

    public function getModalTitle(): string
    {
        return $this->modalTitle;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    public function getModalDialogClass(): string
    {
        return $this->modalDialogClass;
    }
}
