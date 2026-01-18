<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderHelper
{
    public function __construct(
        protected readonly bool $disableFields,
    ) {
    }

    /**
     * @param string[] $disabledFields
     */
    public function disableFieldsByConfigurations(FormBuilderInterface $builder, array $disabledFields): void
    {
        if (!$this->disableFields) {
            return;
        }
        $this->trackFormElements($builder->all(), $disabledFields);
    }

    /**
     * @param array<string, \Symfony\Component\Form\FormBuilderInterface> $elements
     * @param string[] $disabledFields
     */
    protected function trackFormElements(array $elements, array $disabledFields): void
    {
        foreach ($elements as $element) {
            if (in_array($element->getName(), $disabledFields, true)) {
                $element->setDisabled(true);
            }
            $this->trackFormElements($element->all(), $disabledFields);
        }
    }

    public function hasFormDisabledFields(): bool
    {
        return $this->disableFields;
    }
}
