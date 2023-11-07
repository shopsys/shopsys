<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

class ExampleMessage
{
    /**
     * @param string $value
     */
    public function __construct(
        public readonly string $value,
    ) {
    }
}
