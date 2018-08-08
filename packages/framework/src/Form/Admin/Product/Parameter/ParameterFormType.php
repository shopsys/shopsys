<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter;

use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ParameterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter parameter name']),
                        new Constraints\Length(['max' => 100, 'maxMessage' => 'Parameter name cannot be longer than {{ limit }} characters']),
                    ],
                ],
            ])
            ->add('visible', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParameterData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
