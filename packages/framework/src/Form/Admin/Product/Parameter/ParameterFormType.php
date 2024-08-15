<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ParameterFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly UnitFacade $unitFacade,
        private readonly ParameterFacade $parameterFacade,
        private readonly Localization $localization,
    ) {
    }

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
                        new Constraints\NotBlank(['message' => 'Please enter parameter name']),
                        new Constraints\Length(
                            ['max' => 100, 'maxMessage' => 'Parameter name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
            ])
            ->add('parameterType', ChoiceType::class, [
                'required' => true,
                'choices' => Parameter::PARAMETER_TYPES,
            ])
            ->add('unit', ChoiceType::class, [
                'required' => false,
                'choices' => $this->unitFacade->getAll(),
                'placeholder' => t('-- Choose unit --'),
                'choice_label' => 'name',
                'choice_value' => 'id',
            ])
            ->add('orderingPriority', NumberType::class, [
                'required' => true,
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t(
                        'This is used for ordering of parameters on product detail and ordering of parameters on search page when using Luigi\'s Box. For ordering of parameters in filter use settings in the category.',
                    ),
                ],
            ])
            ->add('visible', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ParameterData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUniqueParameterName']),
                ],
            ])
            ->setRequired(['parameter'])
            ->setAllowedTypes('parameter', [Parameter::class, 'null']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniqueParameterName(ParameterData $parameterData, ExecutionContextInterface $context): void
    {
        $form = $context->getRoot();
        $formOptions = $form->getConfig()->getOptions();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null $currentParameter */
        $currentParameter = $formOptions['parameter'];

        foreach ($parameterData->name as $locale => $name) {
            if ($name === null) {
                continue;
            }

            if ($this->parameterFacade->existsParameterByName($name, $locale, $currentParameter)) {
                $context
                    ->buildViolation(t('Parameter with this name already exists for the locale "%locale%".', ['%locale%' => $locale], Translator::VALIDATOR_TRANSLATION_DOMAIN))
                    ->atPath(sprintf('name[%s]', $this->localization->getAdminLocale()))
                    ->addViolation();
            }
        }
    }
}
