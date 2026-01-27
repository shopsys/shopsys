<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchRulesFormType;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractAdvancedSearchFormFactory
{
    public function __construct(
        protected readonly AdvancedSearchFilterRegistry $advancedSearchFilterRegistry,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
    }

    /**
     * @param string $name
     * @param array<int|string, array{subject: string, operator: string|null, value: mixed}> $rulesViewData
     * @param string $entityType
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRulesForm($name, $rulesViewData, string $entityType)
    {
        $options = [
            'csrf_protection' => false,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'entity_type' => $entityType,
        ];
        $formBuilder = $this->formFactory->createNamedBuilder($name, AdvancedSearchRulesFormType::class, null, $options);
        $formBuilder->setMethod('GET');

        foreach ($rulesViewData as $ruleKey => $ruleViewData) {
            $ruleFilter = $this->advancedSearchFilterRegistry->getFilter($entityType, $ruleViewData['subject']);
            $formBuilder->add($this->createRuleFormBuilder($ruleKey, $ruleFilter, $entityType));
        }

        $form = $formBuilder->getForm();
        $form->submit($rulesViewData);

        return $form;
    }

    /**
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface $ruleFilter
     * @param string $entityType
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    protected function createRuleFormBuilder($name, AdvancedSearchFilterInterface $ruleFilter, string $entityType)
    {
        return $this->formFactory->createNamedBuilder((string)$name, FormType::class, null, [
            'data_class' => AdvancedSearchRuleData::class,
        ])
            ->add('subject', ChoiceType::class, [
                'choices' => $this->getSubjectChoices($entityType),
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('operator', ChoiceType::class, [
                'choices' => $this->getFilterOperatorChoices($ruleFilter),
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('value', $ruleFilter->getValueFormType(), $ruleFilter->getValueFormOptions());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface $filter
     * @return string[]
     */
    protected function getFilterOperatorChoices(AdvancedSearchFilterInterface $filter): array
    {
        $choices = [];

        foreach ($filter->getAllowedOperators() as $operator) {
            $choices[$this->advancedSearchOperatorTranslation->translateOperator($operator)] = $operator;
        }

        return $choices;
    }

    /**
     * @param string $entityType
     * @return string[]
     */
    protected function getSubjectChoices(string $entityType)
    {
        $choices = [];

        foreach ($this->advancedSearchFilterRegistry->getFiltersForEntity($entityType) as $filter) {
            $choices[$filter->getLabel()] = $filter->getName();
        }

        return $choices;
    }
}
