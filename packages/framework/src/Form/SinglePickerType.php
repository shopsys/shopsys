<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class SinglePickerType extends AbstractType
{
    public function __construct(
        protected readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new CallbackTransformer(
            static fn ($value) => $value,
            static fn ($value) => $value !== null && $value !== '' ? (int)$value : null,
        ));
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['picker_url'] = $options['picker_url'];
        $view->vars['picker_title'] = $options['picker_title'];
        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['enable_remove'] = $options['enable_remove'];
        $view->vars['inline_label'] = $options['inline_label'];

        $value = $form->getData();

        if ($value === null) {
            return;
        }

        $itemName = $options['item_name'];
        $view->vars['selected_name'] = is_callable($itemName)
            ? $itemName($value)
            : $this->propertyAccessor->getValue($value, $itemName);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'placeholder' => t('Choose'),
            'picker_title' => t('Select'),
            'enable_remove' => true,
            'inline_label' => false,
            'item_name' => 'name',
            'required' => false,
        ]);

        $resolver->setRequired('picker_url');
        $resolver->setAllowedTypes('picker_url', 'string');
        $resolver->setAllowedTypes('picker_title', 'string');
        $resolver->setAllowedTypes('item_name', ['string', 'callable']);
        $resolver->setAllowedTypes('enable_remove', 'bool');
        $resolver->setAllowedTypes('inline_label', 'bool');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'single_picker';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
