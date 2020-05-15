<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Parameter\Value;

use App\Model\Product\Parameter\ParameterValueData;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ParameterValueFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('rgbHex', TextType::class, [
            'required' => false,
            'label' => t('RGB Hex'),
            'constraints' => [
                new Constraints\Regex([
                    'pattern' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                    'message' => t('Chybný formát RGB HEX : #336699 nebo #ABC'),
                ]),
            ],
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ParameterValueData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
