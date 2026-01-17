<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\MessageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SliderParameterValuesUpdateFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', MessageType::class, [
                'data' => t(
                    'You have changed a parameter type which requires conversion of its values to a %type%. Here is a list of values, that needs to be reviewed.',
                    ['%type%' => t('numbers')],
                ),
                'message_level' => MessageType::MESSAGE_LEVEL_WARNING,
            ])
            ->add($builder->create('parameterValues', ParameterValueConversionListType::class, [
                'required' => false,
                'allow_add' => false,
                'allow_delete' => false,
                'entry_type' => ParameterValueConversionFormType::class,
                'error_bubbling' => false,
                'label' => false,
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
