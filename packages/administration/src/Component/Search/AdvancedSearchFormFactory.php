<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

final class AdvancedSearchFormFactory
{
    public const string TEMPLATE_RULE_KEY = '__template__';

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Creates the advanced search rules form (a GET form named "f") pre-filled from the request,
     * always including a hidden template rule used by JS to add new rules.
     */
    public function createForm(SearchConfig $searchConfig, Request $request): FormInterface
    {
        return $this->createRulesForm($searchConfig, $this->createRulesViewData($searchConfig, $request));
    }

    /**
     * Whether the request is an advanced search submission — the rules or the "open advanced search" flag are present.
     */
    public function isSubmitted(Request $request): bool
    {
        return is_array($request->query->all()[SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER] ?? null)
            || $request->query->has(SearchConfig::ADVANCED_SEARCH_FLAG_QUERY_PARAMETER);
    }

    /**
     * Creates the view of a single rule row with the given subject preselected, used to re-render
     * the row when the administrator changes the subject. The row is built inside the rules form,
     * so its inputs keep the "f[...]" names and submit with the rest of the rules.
     */
    public function createRuleFormView(SearchConfig $searchConfig, string $filterName, string $ruleIndex): FormView
    {
        $sanitizedRuleIndex = preg_replace('/\W/', '_', $ruleIndex);
        $rulesViewData = [$sanitizedRuleIndex => $this->createDefaultRuleViewData($filterName)];

        $form = $this->createRulesForm($searchConfig, $rulesViewData);

        return $form->createView()->children[$sanitizedRuleIndex];
    }

    /**
     * @param array<int|string, array{subject: string, operator: string|null, value: mixed}> $rulesViewData
     */
    private function createRulesForm(SearchConfig $searchConfig, array $rulesViewData): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder(
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER,
            FormType::class,
            null,
            [
                'csrf_protection' => false,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ],
        );
        $formBuilder->setMethod('GET');

        foreach ($rulesViewData as $ruleKey => $ruleViewData) {
            $formBuilder->add($this->createRuleFormBuilder($searchConfig, (string)$ruleKey, $ruleViewData['subject']));
        }

        $form = $formBuilder->getForm();
        $form->submit($rulesViewData);

        return $form;
    }

    /**
     * @return array<int|string, array{subject: string, operator: string|null, value: mixed}>
     */
    private function createRulesViewData(SearchConfig $searchConfig, Request $request): array
    {
        $defaultFilterName = $searchConfig->getDefaultFilterName();
        $requestData = $request->query->all()[SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER] ?? null;
        $rulesViewData = [];

        if (is_array($requestData)) {
            foreach ($requestData as $ruleKey => $ruleData) {
                if (
                    !is_array($ruleData)
                    || !is_string($ruleData['subject'] ?? null)
                    || $searchConfig->getFilter($ruleData['subject']) === null
                ) {
                    continue;
                }

                // rule keys become form names, so they must not contain characters Symfony forms reject
                $ruleKey = preg_replace('/\W/', '_', (string)$ruleKey);

                $rulesViewData[$ruleKey] = [
                    'subject' => $ruleData['subject'],
                    'operator' => $ruleData['operator'] ?? null,
                    'value' => $ruleData['value'] ?? null,
                ];
            }
        }

        if (count($rulesViewData) === 0) {
            $rulesViewData[] = $this->createDefaultRuleViewData($defaultFilterName);
        }

        $rulesViewData[self::TEMPLATE_RULE_KEY] = $this->createDefaultRuleViewData($defaultFilterName);

        return $rulesViewData;
    }

    /**
     * @return array{subject: string, operator: null, value: null}
     */
    private function createDefaultRuleViewData(string $filterName): array
    {
        return [
            'subject' => $filterName,
            'operator' => null,
            'value' => null,
        ];
    }

    private function createRuleFormBuilder(
        SearchConfig $searchConfig,
        string $ruleKey,
        string $subjectFilterName,
    ): FormBuilderInterface {
        $filter = $searchConfig->getFilter($subjectFilterName);

        return $this->formFactory->createNamedBuilder($ruleKey, FormType::class)
            ->add('subject', ChoiceType::class, [
                'choices' => $this->getSubjectChoices($searchConfig),
            ])
            ->add('operator', ChoiceType::class, [
                'choices' => $this->getOperatorChoices($filter),
            ])
            ->add('value', $filter->getValueFormType(), $filter->getValueFormOptions());
    }

    /**
     * @return array<string, string>
     */
    private function getSubjectChoices(SearchConfig $searchConfig): array
    {
        $choices = [];

        foreach ($searchConfig->getFilters() as $filter) {
            $choices[$filter->getLabel()] = $filter->getName();
        }

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    private function getOperatorChoices(FilterInterface $filter): array
    {
        $choices = [];

        foreach ($filter->getAllowedOperators() as $operator) {
            $choices[$operator->getLabel()] = $operator->value;
        }

        return $choices;
    }
}
