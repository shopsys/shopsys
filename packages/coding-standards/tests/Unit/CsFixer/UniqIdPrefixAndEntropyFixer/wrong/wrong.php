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
        uniqid();
        uniqid('');
        uniqid('', true);
        uniqid('', false);
        uniqid('prefix', false);
        return true;
    }
}
