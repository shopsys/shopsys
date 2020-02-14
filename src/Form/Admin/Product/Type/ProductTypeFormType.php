<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Type;

use App\Model\Product\Type\ProductTypeData;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ProductTypeFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Prosím vyplňte typ produktu ve všech jazycích.']),
                        new Constraints\Length(['max' => 100, 'maxMessage' => 'Název nesmí být delší než {{ limit }} znaků.']),
                    ],
                ],
            ])
            ->add('akeneoCode', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím vyplňte Akaneo kód']),
                    new Constraints\Length([
                        'max' => 20,
                        'maxMessage' => 'Akaneo kód nesmí být delší než {{ limit }} znaků.',
                    ]),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductTypeData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
