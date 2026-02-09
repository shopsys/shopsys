<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\String\HashGenerator;

final class ErrorIdProvider
{
    private ?string $errorId = null;

    public function __construct(private readonly HashGenerator $hashGenerator)
    {
    }

    public function getErrorId(): string
    {
        if (!$this->errorId) {
            $this->errorId = $this->hashGenerator->generateHash(10);
        }

        return $this->errorId;
    }
}
