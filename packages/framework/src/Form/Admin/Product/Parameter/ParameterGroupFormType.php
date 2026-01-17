<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class ParameterGroupFormType extends AbstractType
{
    public function __construct(
        private readonly ParameterGroupFacade $parameterGroupFacade,
        private readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'label' => 'Name',
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter parameter group name']),
                        new Constraints\Length(
                            ['max' => 100, 'maxMessage' => 'Parameter group name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_parametergroup_list',
                'entity' => $options['parameterGroup'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ParameterGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUniqueParameterGroupName']),
                ],
            ])
            ->setRequired(['parameterGroup'])
            ->setAllowedTypes('parameterGroup', [ParameterGroup::class, 'null']);
    }

    public function validateUniqueParameterGroupName(
        ParameterGroupData $parameterGroupData,
        ExecutionContextInterface $context,
    ): void {
        $form = $context->getRoot();
        $formOptions = $form->getConfig()->getOptions();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null $currentParameterGroup */
        $currentParameterGroup = $formOptions['parameterGroup'];

        foreach ($parameterGroupData->name as $locale => $name) {
            if ($name === null) {
                continue;
            }

            if ($this->parameterGroupFacade->existsParameterGroupByName($name, $locale, $currentParameterGroup)) {
                $context
                    ->buildViolation(t('Parameter group with this name already exists for the locale "%locale%".', ['%locale%' => $locale], Translator::VALIDATOR_TRANSLATION_DOMAIN))
                    ->atPath(sprintf('name[%s]', $this->localization->getCurrentLocaleForTranslatableEntities()))
                    ->addViolation();
            }
        }
    }
}
