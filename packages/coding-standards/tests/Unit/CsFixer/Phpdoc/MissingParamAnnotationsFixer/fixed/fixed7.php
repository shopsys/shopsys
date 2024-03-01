<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Tests;

class SomeParentClassWithAttribute
{
    /**
     * @param string $bar
     */
    public function foo(string $bar): void
    {
    }
}

class SomeClass extends SomeParentClassWithAttribute
{
    /**
     * @param string $bar
     */
    #[\Override]
    public function foo(string $bar): void
    {
    }

    /**
     * @param string $foo
     * @param string $bar
     */
    #[\ReturnTypeWillChange]
    public function bar(string $foo, string $bar): void
    {
    }
}
