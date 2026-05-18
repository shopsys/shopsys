<?php

namespace TestNamespace\TestSubNamespace;

abstract class SomeAbstractClass
{
    private const SOME_CONSTANT = 'value';

    private ?string $internalState = null;

    public function __construct(
        private readonly string $name,
    ) {
    }

    private function internalHelper(): bool
    {
        return true;
    }
}
