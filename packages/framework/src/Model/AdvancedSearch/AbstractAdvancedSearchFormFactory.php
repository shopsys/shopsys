<?php

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation;
use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractAdvancedSearchFormFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig
     */
    protected $advancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation
     */
    protected $advancedSearchFilterTranslation;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation
     */
    protected $advancedSearchOperatorTranslation;

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchConfig $advancedSearchConfig
     * @param \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchFilterTranslation $advancedSearchFilterTranslation
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation $advancedSearchOperatorTranslation
     */
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

    /**
     * @param string $name
     * @param mixed[] $rulesViewData
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRulesForm(string $name, array $rulesViewData): FormInterface
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
    protected function createRuleFormBuilder(string $name, AdvancedSearchFilterInterface $ruleFilter): FormBuilderInterface
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
     * @return array<string, string>
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
     * @return array<string, string>
     */
    protected function getSubjectChoices(): array
    {
        $choices = [];
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $choices[$this->advancedSearchFilterTranslation->translateFilterName(
                $filter->getName()
            )] = $filter->getName();
        }

        return $choices;
    }
}
