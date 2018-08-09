<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Unit;

use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class UnitSettingFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    private $unitFacade;

    public function __construct(UnitFacade $unitFacade)
    {
        $this->unitFacade = $unitFacade;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('defaultUnit', ChoiceType::class, [
                'placeholder' => t('-- Choose unit --'),
                'required' => true,
                'choices' => $this->unitFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose default unit']),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
