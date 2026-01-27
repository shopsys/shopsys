<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchRulesFormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractAdvancedSearchFormFactory
{
    public function __construct(
        protected readonly AdvancedSearchConfig $advancedSearchConfig,
        protected readonly AdvancedSearchFilterTranslation $advancedSearchFilterTranslation,
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
    public function createRulesForm($name, $rulesViewData, $entityType)
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
            $ruleFilter = $this->advancedSearchConfig->getFilter($ruleViewData['subject']);
            $formBuilder->add($this->createRuleFormBuilder($ruleKey, $ruleFilter));
        }

        $form = $formBuilder->getForm();
        $form->submit($rulesViewData);

        return $form;
    }

    protected function createRuleFormBuilder(
        int|string $name,
        AdvancedSearchFilterInterface $ruleFilter,
    ): FormBuilderInterface {
        return $this->formFactory->createNamedBuilder((string)$name, FormType::class, null, [
            'data_class' => AdvancedSearchRuleData::class,
        ])
            ->add('subject', ChoiceType::class, [
                'choices' => $this->getSubjectChoices(),
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
     * @return string[]
     */
    protected function getSubjectChoices(): array
    {
        $choices = [];

        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $choices[$this->advancedSearchFilterTranslation->translateFilterName(
                $filter->getName(),
            )] = $filter->getName();
        }

        return $choices;
    }
}
