<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\WriteTool\data;

class TestApprovableWriteToolArguments
{
    public function __construct(
        public readonly int $articleId,
        public readonly string $name,
    ) {
    }
}
