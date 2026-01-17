<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Unit;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class UnitSettingFormType extends AbstractType
{
    public function __construct(private readonly UnitFacade $unitFacade)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultUnit', ChoiceType::class, [
                'label' => 'Default unit',
                'placeholder' => '-- Choose unit --',
                'required' => true,
                'choices' => $this->unitFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'help' => t('Default unit will be used when creating new product'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose default unit']),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save default unit',
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
