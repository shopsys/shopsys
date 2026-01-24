<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

class RuleFormViewDataFactory
{
    public const TEMPLATE_RULE_FORM_KEY = '__template__';

    /**
     * @param array<int|string, array<string, mixed>>|null $requestData
     * @return array<int|string, array{subject: string, operator: string|null, value: mixed}>
     */
    public function createFromRequestData(string $defaultFilterName, ?array $requestData = null): array
    {
        if ($requestData === null) {
            $searchRulesViewData = [];
        } else {
            $searchRulesViewData = $requestData;
        }

        if (count($searchRulesViewData) === 0) {
            $searchRulesViewData[] = $this->createDefault($defaultFilterName);
        }

        $searchRulesViewData[self::TEMPLATE_RULE_FORM_KEY] = $this->createDefault($defaultFilterName);

        return $searchRulesViewData;
    }

    /**
     * @return array{subject: string, operator: null, value: null}
     */
    public function createDefault(string $filterName): array
    {
        return [
            'subject' => $filterName,
            'operator' => null,
            'value' => null,
        ];
    }
}
