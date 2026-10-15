<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form of the whole content document, its data is an array with the list of "blocks".
 *
 * The form is rendered without a <form> element, because the editor lives inside the form of the edited entity,
 * so the Live Component cannot bind the fields by their names and finishView() binds each of them to the model.
 */
final class ContentDocumentType extends AbstractType
{
    private const string DEBOUNCED_MODEL_MODIFIER = 'debounce(300)';
    // typing is slower than in an input, the rich text editor itself is ignored by the render (data-live-ignore), only the preview follows
    private const string WYSIWYG_MODEL_MODIFIER = 'debounce(500)';

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'variables' => [],
                'csrf_protection' => false,
            ])
            ->setAllowedTypes('variables', 'array');
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blocks', CollectionType::class, [
            'entry_type' => ContentBlockFormType::class,
            'entry_options' => ['variables' => $options['variables']],
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,
        ]);
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->bindFieldsToModel($view);
    }

    private function bindFieldsToModel(FormView $view): void
    {
        foreach ($view->children as $child) {
            // the products picker is not a model field, product_ids_sync_controller.js updates the model for it
            if (in_array('product_ids', $child->vars['block_prefixes'], true)) {
                continue;
            }

            if ($child->vars['compound']) {
                $this->bindFieldsToModel($child);

                continue;
            }

            $modifier = in_array('wysiwyg', $child->vars['block_prefixes'], true)
                ? self::WYSIWYG_MODEL_MODIFIER
                : self::DEBOUNCED_MODEL_MODIFIER;
            $child->vars['attr']['data-model'] = $modifier . '|' . $child->vars['full_name'];
        }
    }
}
