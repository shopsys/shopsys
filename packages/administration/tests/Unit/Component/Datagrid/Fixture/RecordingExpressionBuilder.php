<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;

/**
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder<\Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\RecordedExpression>
 */
final class RecordingExpressionBuilder extends AbstractExpressionBuilder
{
    public function __construct()
    {
        parent::__construct(new ExpressionOperatorEnum());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function compare(string $path, string $operator, mixed $value): RecordedExpression
    {
        return new RecordedExpression(sprintf('%s %s %s', $path, $operator, json_encode($value, JSON_UNESCAPED_UNICODE)));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function not(object $expression): RecordedExpression
    {
        return new RecordedExpression(sprintf('NOT(%s)', $expression->record));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function andX(object ...$expressions): RecordedExpression
    {
        return $this->combine('AND', $expressions);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function orX(object ...$expressions): RecordedExpression
    {
        return $this->combine('OR', $expressions);
    }

    /**
     * @param \Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\RecordedExpression[] $expressions
     */
    private function combine(string $logicalOperator, array $expressions): RecordedExpression
    {
        $records = array_map(
            static fn (RecordedExpression $expression): string => $expression->record,
            $expressions,
        );

        return new RecordedExpression(sprintf('%s(%s)', $logicalOperator, implode(', ', $records)));
    }
}
