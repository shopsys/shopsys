<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

abstract class AbstractAdvancedSearchFormFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig $advancedSearchConfig
     * @param \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation $advancedSearchFilterTranslation
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation
     */
    public function __construct(
        protected readonly AdvancedSearchConfig $advancedSearchConfig,
        protected readonly AdvancedSearchFilterTranslation $advancedSearchFilterTranslation,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation,
    ) {
    }

    /**
     * @param string $name
     * @param array $rulesViewData
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRulesForm($name, $rulesViewData)
    {
        $options = [
            'csrf_protection' => false,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
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

    /**
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface $ruleFilter
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    protected function createRuleFormBuilder($name, AdvancedSearchFilterInterface $ruleFilter)
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, null, [
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
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface $filter
     * @return string[]
     */
    protected function getFilterOperatorChoices(AdvancedSearchFilterInterface $filter)
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
    protected function getSubjectChoices()
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
