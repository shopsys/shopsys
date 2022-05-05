<?php

namespace TestNamespace\TestSubNamespace;

class SomeClass
{
    /**
     * @var int
     */
    public int $field;

    /**
     * @return bool
     */
    protected function method(): bool
    {
        uniqid('prefix', true);
        return true;
    }
}
