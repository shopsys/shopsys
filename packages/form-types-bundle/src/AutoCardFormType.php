<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

// @todo: validate if we want to use this in the future, or if we want to use the FormType from Symfony
abstract class AutoCardFormType extends AbstractType
{
    #[Override]
    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildFormFields($builder, $options);

        $this->autoWrapUnwrappedFields($builder);
    }

    abstract protected function buildFormFields(FormBuilderInterface $builder, array $options): void;

    private function autoWrapUnwrappedFields(FormBuilderInterface $builder): void
    {
        $unwrappedFields = [];
        $groupTypes = $this->getGroupTypeClasses(); // Your GroupType classes

        foreach ($builder->all() as $name => $childBuilder) {
            $fieldType = $childBuilder->getType()->getInnerType();

            // Skip if field is already a group/card type
            if ($this->isGroupType($fieldType, $groupTypes)) {
                continue;
            }

            // Skip specific types that shouldn't be wrapped (like ActionBarType)
            if ($this->shouldSkipWrapping($fieldType)) {
                continue;
            }

            $unwrappedFields[$name] = $childBuilder;
        }

        // Wrap unwrapped fields in a default card
        if (!empty($unwrappedFields)) {
            $this->wrapFieldsInCard($builder, $unwrappedFields);
        }
    }

    private function isGroupType($fieldType, array $groupTypes): bool
    {
        foreach ($groupTypes as $groupTypeClass) {
            if ($fieldType instanceof $groupTypeClass || get_class($fieldType) === $groupTypeClass) {
                return true;
            }
        }
        return false;
    }

    private function shouldSkipWrapping($fieldType): bool
    {
        $skipTypes = [
            ActionBarType::class,
            // Add other types that shouldn't be wrapped
        ];

        foreach ($skipTypes as $skipType) {
            if ($fieldType instanceof $skipType || get_class($fieldType) === $skipType) {
                return true;
            }
        }
        return false;
    }

    private function wrapFieldsInCard(FormBuilderInterface $builder, array $fieldsToWrap): void
    {
        $cardName = $this->getDefaultCardName();
        $cardTitle = $this->getDefaultCardTitle();

        // Create the card group
        $cardBuilder = $builder->create($cardName, GroupType::class, [
            'label' => $cardTitle,
        ]);

        // Move fields to the card
        foreach ($fieldsToWrap as $name => $childBuilder) {
            $builder->remove($name);
            $cardBuilder->add(
                $name,
                get_class($childBuilder->getType()->getInnerType()),
                $childBuilder->getOptions()
            );
        }

        $builder->add($cardBuilder, null, ['position' => 'first']);
    }

    protected function getGroupTypeClasses(): array
    {
        return [
            GroupType::class, // Your GroupType
            // Add other group types if you have them
        ];
    }

    protected function getDefaultCardName(): string
    {
        return 'main_content';
    }

    protected function getDefaultCardTitle(): string
    {
        return t('Form Fields'); // Or return null for no title
    }
}
