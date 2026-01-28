<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotAllowedException;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchFilterOperatorNotFoundException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;

abstract class AbstractAdvancedSearchFilter implements AdvancedSearchFilterInterface
{
    protected const string DEFAULT_EMPTY_SEARCH_VALUE = '%';

    public function __construct(
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    abstract public function getName(): string;

    /**
     * {@inheritdoc}
     */
    #[Override]
    abstract public function getLabel(): string;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_CONTAINS,
            self::OPERATOR_NOT_CONTAINS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    protected function getDqlOperator(string $operator): string
    {
        if (!in_array($operator, $this->getAllowedOperators(), true)) {
            throw new AdvancedSearchFilterOperatorNotAllowedException($operator, $this->getAllowedOperators());
        }

        return match ($operator) {
            self::OPERATOR_CONTAINS => 'LIKE',
            self::OPERATOR_NOT_CONTAINS => 'NOT LIKE',
            self::OPERATOR_GT => '>',
            self::OPERATOR_LT => '<',
            self::OPERATOR_GTE => '>=',
            self::OPERATOR_LTE => '<=',
            self::OPERATOR_IS => '=',
            self::OPERATOR_IS_NOT => '!=',
            default => throw new AdvancedSearchFilterOperatorNotFoundException($operator),
        };
    }

    protected function getSearchValue(AdvancedSearchRuleData $ruleData): string
    {
        if ($ruleData->value === null || $ruleData->value === '') {
            return static::DEFAULT_EMPTY_SEARCH_VALUE;
        }

        return $this->databaseSearchingHelper->getFullTextLikeSearchString($ruleData->value);
    }
}
