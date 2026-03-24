<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractAdvancedSearchFacade
{
    public const string RULES_FORM_NAME = 'as';

    public function __construct(
        protected readonly AdvancedSearchFormFactory $advancedSearchFormFactory,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
    ) {
    }

    abstract protected function getDefaultFilterName(): string;

    abstract public static function getEntityType(): string;

    public function createAdvancedSearchForm(Request $request): FormInterface
    {
        $rawRulesData = is_array($request->query->all()[static::RULES_FORM_NAME] ?? null)
            ? $request->query->all(static::RULES_FORM_NAME)
            : [];

        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            $this->getDefaultFilterName(),
            $rawRulesData,
        );

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesFormData, static::getEntityType());
    }

    public function isAdvancedSearchFormSubmitted(Request $request): bool
    {
        return $request->query->has(static::RULES_FORM_NAME) || $request->query->has('advancedSearch');
    }

    public function createRuleForm(string $filterName, string $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->advancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData, static::getEntityType());
    }

    public function getRuleFormTemplatePath(): string
    {
        return '@ShopsysAdministration/content/advancedSearch/ruleForm.html.twig';
    }
}
