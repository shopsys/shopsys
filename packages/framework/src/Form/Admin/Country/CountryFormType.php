<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Country;

use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CountryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter country name']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Country name cannot be longer than {{ limit }} characters']),
                ], ]
            )
            ->add('code', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(['max' => 2, 'maxMessage' => 'Country code cannot be longer than {{ limit }} characters']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CountryData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
