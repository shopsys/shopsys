<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GrapesJsMailType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer $wysiwygCdnDataTransformer
     */
    public function __construct(
        private readonly WysiwygCdnDataTransformer $wysiwygCdnDataTransformer,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addViewTransformer($this->wysiwygCdnDataTransformer);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['body_variables'] = $options['body_variables'];
        $view->vars['custom_plugins'] = $options['custom_plugins'];

        parent::buildView($view, $form, $options);
    }

    /**
     * @return string
     */
    #[Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['body_variables', 'custom_plugins'])
            ->setAllowedTypes('body_variables', 'array')
            ->setAllowedTypes('custom_plugins', 'array')
            ->setDefaults([
                'body_variables' => [],
                'custom_plugins' => [],
            ]);
    }
}
