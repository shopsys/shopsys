<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\String\HashGenerator;

class ErrorIdProvider
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @var string|null
     */
    private $errorId;

    public function __construct(HashGenerator $hashGenerator)
    {
        $this->hashGenerator = $hashGenerator;
    }

    public function getErrorId(): string
    {
        if (!$this->errorId) {
            $this->errorId = $this->hashGenerator->generateHash(32);
        }
        return $this->errorId;
    }
}
