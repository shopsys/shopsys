<?php

namespace TestNamespace\TestSubNamespace;

final readonly class SomeFinalReadonlyClass
{
    public function __construct(
        private string $field,
    ) {
    }

    private function method(): bool
    {
        return true;
    }
}
