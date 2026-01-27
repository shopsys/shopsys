<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAdvancedSearchFacade
{
    public const string RULES_FORM_NAME = 'as';

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFormFactory $advancedSearchFormFactory
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory $ruleFormViewDataFactory
     */
    public function __construct(
        protected readonly AbstractAdvancedSearchFormFactory $advancedSearchFormFactory,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
    ) {
    }

    /**
     * @return string
     */
    abstract protected function getDefaultFilterName(): string;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAdvancedSearchForm(Request $request): FormInterface
    {
        $rawRulesData = $request->query->all(static::RULES_FORM_NAME);

        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            $this->getDefaultFilterName(),
            $rawRulesData,
        );

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function isAdvancedSearchFormSubmitted(Request $request): bool
    {
        return $request->query->has(static::RULES_FORM_NAME);
    }

    /**
     * @param string $filterName
     * @param string $index
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRuleForm(string $filterName, string $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }
}
