<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GrapesJsType extends AbstractType
{
    public const GRAPESJS_TEMPLATE_PATH = '/grapesjs-template';

    public function __construct(
        private readonly WysiwygCdnDataTransformer $wysiwygCdnDataTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addViewTransformer($this->wysiwygCdnDataTransformer);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['allow_products'] = $options['allow_products'];

        parent::buildView($view, $form, $options);
    }

    #[Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['allow_products'])
            ->setAllowedTypes('allow_products', 'boolean')
            ->setDefaults([
                'allow_products' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'js-grapesjs_textarea',
                    ],
                ],
            ]);
    }
}
