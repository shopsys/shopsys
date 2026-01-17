<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\AST\Functions\ConcatFunction;
use Doctrine\ORM\Query\AST\GeneralCaseExpression;
use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\AST\OrderByItem;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\TreeWalkerAdapter;
use Override;
use Shopsys\FrameworkBundle\Component\CollateExpressionNode;

class CollationOrderByWalker extends TreeWalkerAdapter
{
    /**
     * @return array<string>
     */
    protected function getSupportedTypes(): array
    {
        return [
            Types::ASCII_STRING,
            Types::STRING,
            Types::TEXT,
        ];
    }

    /**
     * @param \Doctrine\ORM\Query\AST\PathExpression|mixed $expression
     */
    protected function shouldApplyCollation(mixed $expression): bool
    {
        if (!$expression instanceof PathExpression && !is_string($expression)) {
            return false;
        }

        $queryComponents = $this->getQueryComponents();

        if (is_string($expression)) {
            $dqlAlias = $expression;
        } else {
            $dqlAlias = $expression->identificationVariable;
        }

        if (!isset($queryComponents[$dqlAlias])) {
            return false;
        }

        $fieldData = $queryComponents[$dqlAlias];

        if (array_key_exists('metadata', $fieldData)) {
            $metadata = $queryComponents[$dqlAlias]['metadata'];
            $fieldName = $expression->field;

            if (!$metadata->hasField($fieldName)) {
                return false;
            }

            $fieldType = $metadata->getTypeOfField($fieldName);

            return in_array($fieldType, $this->getSupportedTypes(), true);
        }

        if (array_key_exists('resultVariable', $fieldData)) {
            $resultVariable = $fieldData['resultVariable'];

            if ($resultVariable instanceof ConcatFunction) {
                return true;
            }

            if ($resultVariable instanceof GeneralCaseExpression && $resultVariable->elseScalarExpression instanceof ConcatFunction) {
                return true;
            }

            if ($resultVariable instanceof PathExpression && $this->isPathExpressionFieldString($queryComponents, $resultVariable)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function walkSelectStatement(SelectStatement $ast): void
    {
        $localeCollation = $this->_getQuery()->getHint('collation');

        if (!$localeCollation || !($ast->orderByClause instanceof OrderByClause)) {
            return;
        }

        foreach ($ast->orderByClause->orderByItems as $orderByItem) {
            if ($orderByItem instanceof OrderByItem && $this->shouldApplyCollation($orderByItem->expression)) {
                $orderByItem->expression = new CollateExpressionNode(
                    $orderByItem->expression,
                    $localeCollation,
                );
            }
        }
    }

    /**
     * @param array<string, array> $queryComponents
     */
    protected function isPathExpressionFieldString(array $queryComponents, PathExpression $fieldPathExpression): bool
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $queryComponents[$fieldPathExpression->identificationVariable]['metadata'];
        $fieldData = $metadata->getFieldMapping($fieldPathExpression->field);

        return $fieldData['type'] === 'string';
    }
}
