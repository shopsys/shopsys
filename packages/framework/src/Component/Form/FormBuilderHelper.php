<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use Symfony\Component\Form\FormBuilderInterface;

class FormBuilderHelper
{
    /**
     * @param bool $disableFields
     */
    public function __construct(
        protected readonly bool $disableFields,
    ) {
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
    protected function trackFormElements(array $elements, array $disabledFields): void
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
