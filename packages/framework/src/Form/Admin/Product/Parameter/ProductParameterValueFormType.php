<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter;

use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ProductParameterValueFormType extends AbstractType
{
    public const string VALIDATION_GROUP_TYPE_SLIDER = 'typeSlider';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     */
    public function __construct(private readonly ParameterFacade $parameterFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameter', ChoiceType::class, [
                'required' => true,
                'choices' => $this->parameterFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose parameter']),
                ],
            ])
            ->add('valueTextsByLocale', LocalizedType::class, [
                'required' => true,
                'main_constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter parameter value']),
                ],
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            ['max' => 255, 'maxMessage' => 'Parameter value cannot be longer than {{ limit }} characters'],
                        ),
                        new Constraints\Type([
                            'type' => 'numeric',
                            'message' => 'Parameter value must be numeric',
                            'groups' => [static::VALIDATION_GROUP_TYPE_SLIDER],
                        ]),
                    ],
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data_class' => ProductParameterValuesLocalizedData::class,
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData $productParameterValuesLocalizedData */
                $productParameterValuesLocalizedData = $form->getData();

                if ($productParameterValuesLocalizedData->parameter->isSlider()) {
                    $validationGroups[] = static::VALIDATION_GROUP_TYPE_SLIDER;
                }

                return $validationGroups;
            },
        ]);
    }
}
