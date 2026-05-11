<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Form;

use Shopsys\AdministrationBundle\Component\Crud\Form\Exception\CrudFormAlreadyConfiguredException;
use Shopsys\AdministrationBundle\Component\Crud\Form\Exception\CrudFormNotConfiguredException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class CrudFormConfigurator
{
    protected CrudFormMode $mode = CrudFormMode::UNCONFIGURED;

    /**
     * @var class-string<\Symfony\Component\Form\FormTypeInterface>|null
     */
    protected ?string $formTypeClass = null;

    /**
     * @var array<string, mixed>
     */
    protected array $formTypeOptions = [];

    /**
     * @var array<string, mixed>
     */
    protected array $formOptions = [];

    protected ?FormBuilderInterface $builder = null;

    public function __construct(
        protected readonly FormFactoryInterface $formFactory,
        protected readonly object $data,
    ) {
    }

    /**
     * Configures the form to use an existing FormType class
     *
     * @param class-string<\Symfony\Component\Form\FormTypeInterface> $formTypeClass #FormType
     * @param array<string, mixed> $options #FormOption values
     */
    public function useFormType(string $formTypeClass, array $options = []): void
    {
        if ($this->mode !== CrudFormMode::UNCONFIGURED) {
            throw CrudFormAlreadyConfiguredException::cannotSwitchMode($this->mode);
        }

        $this->formTypeClass = $formTypeClass;
        $this->formTypeOptions = $options;
        $this->mode = CrudFormMode::FORM_TYPE;
    }

    /**
     * Returns a FormBuilderInterface for inline form definition
     *
     * Calling this method multiple times returns the same builder instance.
     */
    public function useBuilder(): FormBuilderInterface
    {
        if ($this->mode === CrudFormMode::FORM_TYPE) {
            throw CrudFormAlreadyConfiguredException::cannotSwitchMode($this->mode);
        }

        if ($this->builder === null) {
            $this->builder = $this->formFactory->createBuilder(data: $this->data, options: $this->formOptions);
            $this->mode = CrudFormMode::BUILDER;
        }

        return $this->builder;
    }

    /**
     * Sets a form option that will be applied when the form is built
     *
     * For FormType mode, the option is merged with (and overrides) options passed to useFormType().
     * For Builder mode, options must be set before the first useBuilder() call.
     */
    public function setFormOption(string $name, mixed $value): static
    {
        if ($this->builder !== null) {
            throw CrudFormAlreadyConfiguredException::cannotSetOptionAfterBuilderCreated();
        }

        $this->formOptions[$name] = $value;

        return $this;
    }

    /**
     * Returns a form option value
     *
     * Checks options set via setFormOption() first, then falls back to options
     * passed to useFormType().
     */
    public function getFormOption(string $name, mixed $default = null): mixed
    {
        return $this->formOptions[$name] ?? $this->formTypeOptions[$name] ?? $default;
    }

    /**
     * Builds and returns the configured Form
     */
    public function buildForm(): FormInterface
    {
        return match ($this->mode) {
            CrudFormMode::FORM_TYPE => $this->formFactory->create($this->formTypeClass, $this->data, array_merge($this->formTypeOptions, $this->formOptions)),
            CrudFormMode::BUILDER => $this->builder->getForm(),
            CrudFormMode::UNCONFIGURED => throw new CrudFormNotConfiguredException(),
        };
    }

    public function getMode(): CrudFormMode
    {
        return $this->mode;
    }
}
