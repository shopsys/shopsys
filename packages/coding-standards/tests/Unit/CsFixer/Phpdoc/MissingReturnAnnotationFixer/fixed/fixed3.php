<?php

class SomeClass
{
    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function function1(): int
    {
        return 0;
    }
}
