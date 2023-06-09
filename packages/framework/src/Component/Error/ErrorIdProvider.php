<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class ErrorIdProvider
{
    protected ?string $errorId = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(protected readonly HashGenerator $hashGenerator)
    {
    }

    /**
     * @return string
     */
    public function getErrorId(): string
    {
        if (!$this->errorId) {
            $this->errorId = $this->hashGenerator->generateHash(10);
        }
        return $this->errorId;
    }
}
