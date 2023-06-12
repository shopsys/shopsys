<?php

declare(strict_types=1);

namespace App\Component\Form;

use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderHelper
{
    /**
     * @param bool $disableFields
     */
    public function __construct(private bool $disableFields)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $disabledFields
     */
    public function disableFieldsByConfigurations(FormBuilderInterface $builder, array $disabledFields): void
    {
        if (!$this->disableFields) {
            return;
        }
        $this->trackFormElements($builder->all(), $disabledFields);
    }

    /**
     * @param array $elements
     * @param array $disabledFields
     */
    private function trackFormElements(array $elements, array $disabledFields): void
    {
        foreach ($elements as $element) {
            /** @var \Ivory\OrderedForm\Builder\OrderedFormBuilder $element */
            if (in_array($element->getName(), $disabledFields, true)) {
                $element->setDisabled(true);
            }
            $this->trackFormElements($element->all(), $disabledFields);
        }
    }

    /**
     * @return bool
     */
    public function hasFormDisabledFields(): bool
    {
        return $this->disableFields;
    }
}
