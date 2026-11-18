<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

final readonly class RecordedExpression
{
    public function __construct(
        public string $record,
    ) {
    }
}
