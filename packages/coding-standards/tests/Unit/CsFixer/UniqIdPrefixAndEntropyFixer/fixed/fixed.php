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
        uniqid('id', true);
        uniqid('id', true);
        uniqid('id', true);
        uniqid('id', true);
        uniqid('prefix', true);
        return true;
    }
}
