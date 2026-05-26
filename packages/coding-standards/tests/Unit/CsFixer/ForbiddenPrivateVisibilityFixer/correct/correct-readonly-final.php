<?php

namespace TestNamespace\TestSubNamespace;

readonly final class SomeReadonlyFinalClass
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
