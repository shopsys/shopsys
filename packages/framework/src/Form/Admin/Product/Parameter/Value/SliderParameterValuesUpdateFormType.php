<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SliderParameterValuesUpdateFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add($builder->create('parameterValues', ParameterValueConversionListType::class, [
                'required' => false,
                'allow_add' => false,
                'allow_delete' => false,
                'entry_type' => ParameterValueConversionFormType::class,
                'error_bubbling' => false,
                'render_form_row' => false,
                'entry_options' => [
                    'type' => 'numeric',
                ],
                'data' => $options['data'],
            ]));

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_parameter_list',
            'save_label' => t('Save changes'),
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
