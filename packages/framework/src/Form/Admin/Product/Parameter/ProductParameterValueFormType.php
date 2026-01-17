<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter;

use Override;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValuesLocalizedData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductParameterValueFormType extends AbstractType
{
    public const string VALIDATION_GROUP_TYPE_SLIDER = 'typeSlider';

    public function __construct(
        private readonly ParameterFacade $parameterFacade,
        private readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parameter', ChoiceType::class, [
                'required' => true,
                'choices' => $this->parameterFacade->getAllWithTranslations($this->localization->getCurrentLocaleForTranslatableEntities()),
                'choice_label' => function (Parameter $parameter) {
                    return $parameter->getName() .
                    ' [' . $this->getGroupName($parameter) . ']';
                },
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose parameter'),
                ],
                'group_by' => function (Parameter $parameter) {
                    return $this->getGroupName($parameter);
                },
            ])
            ->add('valueTextsByLocale', LocalizedType::class, [
                'required' => true,
                'main_constraints' => [
                    new Constraints\NotBlank(message: 'Please enter parameter value'),
                ],
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Parameter value cannot be longer than {{ limit }} characters',
                        ),
                        new Constraints\Type(
                            type: 'numeric',
                            message: 'Parameter value must be numeric',
                            groups: [static::VALIDATION_GROUP_TYPE_SLIDER],
                        ),
                    ],
                ],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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

    protected function getGroupName(Parameter $parameter): string
    {
        return $parameter->getGroup()?->getName($this->localization->getCurrentLocaleForTranslatableEntities()) ?? t('No group');
    }
}
