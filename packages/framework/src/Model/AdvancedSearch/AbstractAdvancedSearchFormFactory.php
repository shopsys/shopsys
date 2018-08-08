<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractAdvancedSearchFormFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig
     */
    private $advancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation
     */
    private $advancedSearchFilterTranslation;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation
     */
    private $advancedSearchOperatorTranslation;

    public function __construct(
        AdvancedSearchConfig $advancedSearchConfig,
        AdvancedSearchFilterTranslation $advancedSearchFilterTranslation,
        FormFactoryInterface $formFactory,
        AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation
    ) {
        $this->advancedSearchConfig = $advancedSearchConfig;
        $this->advancedSearchFilterTranslation = $advancedSearchFilterTranslation;
        $this->formFactory = $formFactory;
        $this->advancedSearchOperatorTranslation = $advancedSearchOperatorTranslation;
    }
    
    public function createRulesForm(string $name, array $rulesViewData): \Symfony\Component\Form\FormInterface
    {
        $options = [
            'csrf_protection' => false,
            'attr' => ['novalidate' => 'novalidate'],
        ];
        $formBuilder = $this->formFactory->createNamedBuilder($name, FormType::class, null, $options);
        $formBuilder->setMethod('GET');

        foreach ($rulesViewData as $ruleKey => $ruleViewData) {
            $ruleFilter = $this->advancedSearchConfig->getFilter($ruleViewData['subject']);
            $formBuilder->add($this->createRuleFormBuilder($ruleKey, $ruleFilter));
        }

        $form = $formBuilder->getForm();
        $form->submit($rulesViewData);

        return $form;
    }
    
    private function createRuleFormBuilder(string $name, AdvancedSearchFilterInterface $ruleFilter): \Symfony\Component\Form\FormBuilderInterface
    {
        $filterFormBuilder = $this->formFactory->createNamedBuilder($name, FormType::class, null, [
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

        return $filterFormBuilder;
    }

    /**
     * @return string[]
     */
    private function getFilterOperatorChoices(AdvancedSearchFilterInterface $filter): array
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
    private function getSubjectChoices(): array
    {
        $choices = [];
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $choices[$this->advancedSearchFilterTranslation->translateFilterName($filter->getName())] = $filter->getName();
        }

        return $choices;
    }
}
