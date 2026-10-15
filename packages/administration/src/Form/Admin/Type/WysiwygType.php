<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\AdministrationBundle\Form\Admin\Type\Transformer\SanitizedHtmlTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Rich text editor (Tiptap) holding sanitized HTML.
 * The "variables" are placeholders the editor offers for insertion, e.g. in mail templates.
 */
final class WysiwygType extends AbstractType
{
    public function __construct(
        private readonly SanitizedHtmlTransformer $sanitizedHtmlTransformer,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'variables' => [],
            ])
            ->setAllowedTypes('variables', 'array');
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->sanitizedHtmlTransformer);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['variables'] = $options['variables'];
    }

    #[Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
