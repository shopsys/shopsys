<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CategorySeo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReadyCategorySeoCombinationSelectorFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData */
        $categorySeoFiltersData = $options['categorySeoFiltersData'];
        $parameterValueChoices = $options['parameterValueChoices'];
        $flagChoices = $options['flagChoices'];
        $orderingChoices = $options['orderingChoices'];

        $basicInformationGroup = $builder->create('general', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($categorySeoFiltersData->useFlags) {
            $basicInformationGroup->add('flagId', ChoiceType::class, [
                'label' => t('Flag'),
                'choices' => $flagChoices,
                'placeholder' => t('-- Choose a flag --'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }

        if ($categorySeoFiltersData->useOrdering && $orderingChoices !== []) {
            $basicInformationGroup->add('ordering', ChoiceType::class, [
                'label' => t('Ordering'),
                'choices' => $orderingChoices,
                'placeholder' => t('-- Choose an ordering --'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }

        $builder->add($basicInformationGroup);

        if ($categorySeoFiltersData->parameters !== []) {
            $parametersGroup = $builder->create('parameters', GroupType::class, [
                'label' => 'Parameters',
            ]);

            foreach ($categorySeoFiltersData->parameters as $parameter) {
                $parametersGroup->add('parameter_' . $parameter->getId(), ChoiceType::class, [
                    'label' => $parameter->getName(),
                    'choices' => $parameterValueChoices[$parameter->getId()] ?? [],
                    'placeholder' => t('-- Choose a value --'),
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);
            }

            $builder->add($parametersGroup);
        }

        $builder->add('actionBar', ActionBarType::class, [
            'back_url' => $options['backLink'],
            'back_label' => t('Back to parameter selection'),
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['categorySeoFiltersData', 'backLink'])
            ->setDefaults([
                'parameterValueChoices' => [],
                'flagChoices' => [],
                'orderingChoices' => [],
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setAllowedTypes('categorySeoFiltersData', CategorySeoFiltersData::class)
            ->setAllowedTypes('parameterValueChoices', 'array')
            ->setAllowedTypes('flagChoices', 'array')
            ->setAllowedTypes('orderingChoices', 'array')
            ->setAllowedTypes('backLink', 'string');
    }
}
