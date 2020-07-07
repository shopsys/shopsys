<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Parameter\Unit;

use App\Model\Product\Parameter\Unit\ParameterUnit;
use App\Model\Product\Parameter\Unit\ParameterUnitData;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterUnitFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /** @var \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnit */
        $parameterUnit = $options['data'];

        $builder->add('name', LocalizedType::class, [
            'required' => false,
            'label' => $parameterUnit->akeneoCode,
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('parameterUnit')
            ->setAllowedTypes('parameterUnit', [ParameterUnit::class, 'null'])
            ->setDefaults([
                'data_class' => ParameterUnitData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
