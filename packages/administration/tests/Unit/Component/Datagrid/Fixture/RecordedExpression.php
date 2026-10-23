<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

/**
 * The expression of `RecordingExpressionBuilder` — a readable record of the calls that built it.
 */
final readonly class RecordedExpression
{
    public function __construct(
        public string $record,
    ) {
    }
}
