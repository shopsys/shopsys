<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Transformers\WysiwygCdnDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrapesJsType extends AbstractType
{
    public const GRAPESJS_TEMPLATE_PATH = '/grapesjs-template';

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addViewTransformer($this->wysiwygCdnDataTransformer);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_products'] = $options['allow_products'];

        parent::buildView($view, $form, $options);
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
