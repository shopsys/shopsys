<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Flag;

use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class FlagFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter flag name in all languages']),
                        new Constraints\Length(['max' => 100, 'maxMessage' => 'Flag name cannot be longer than {{ limit }} characters']),
                    ],
                ],
            ])
            ->add('rgbColor', ColorPickerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter flag color']),
                    new Constraints\Length([
                        'max' => 7,
                        'maxMessage' => 'Flag color in must be in valid hexadecimal code e.g. #3333ff',
                    ]),
                ],
            ])
            ->add('visible', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FlagData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
