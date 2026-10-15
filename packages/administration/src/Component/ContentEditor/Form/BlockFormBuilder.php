<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\Form;

use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\FieldInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class BlockFormBuilder
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @param array<int, array{label: string, placeholder: string, isRequired: bool}> $variables placeholders offered in the rich text editors, replace the ones of the fields when not empty
     */
    public function createDataForm(ContentBlockInterface $block, array $variables): FormInterface
    {
        $customFormType = $block->getFormType();

        if ($customFormType !== null) {
            return $this->formFactory->createNamed('data', $customFormType, null, [
                'label' => false,
                'auto_initialize' => false,
            ]);
        }

        $builder = $this->formFactory->createNamedBuilder('data', FormType::class, null, [
            'label' => false,
            'auto_initialize' => false,
        ]);

        foreach ($block->getFields() as $field) {
            $builder->add($field->getName(), $field->getFormType(), $this->getFieldFormOptions($field, $variables));
        }

        return $builder->getForm();
    }

    /**
     * @param array<int, array{label: string, placeholder: string, isRequired: bool}> $variables
     * @return array<string, mixed>
     */
    private function getFieldFormOptions(FieldInterface $field, array $variables): array
    {
        $options = $field->getFormOptions();

        if ($field instanceof WysiwygField && $variables !== []) {
            $options['variables'] = $variables;
        }

        return $options;
    }
}
