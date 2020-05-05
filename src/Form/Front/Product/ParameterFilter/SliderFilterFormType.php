<?php

declare(strict_types=1);

namespace App\Form\Front\Product\ParameterFilter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class SliderFilterFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['slider_config'];

        $builder->add('min', TextType::class, [
            'attr' => ['placeholder' => $config['min']],
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => $config['min'],
                ])
            ]
        ]);
        $builder->add('max', TextType::class, [
            'attr' => ['placeholder' => $config['max']],
            'constraints' => [
                new LessThanOrEqual([
                    'value' => $config['max'],
                ])
            ]
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['isSlider'] = true;
        $view->vars['min'] = $options['slider_config']['min'];
        $view->vars['max'] = $options['slider_config']['max'];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('slider_config')
            ->setAllowedTypes('slider_config', 'array');
    }
}
