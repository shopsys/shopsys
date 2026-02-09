<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

class TransMethodSpecification
{
    public function __construct(
        protected string $methodName,
        protected int $messageIdArgumentIndex = 0,
        protected ?int $domainArgumentIndex = null,
    ) {
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getMessageIdArgumentIndex(): int
    {
        return $this->messageIdArgumentIndex;
    }

    public function getDomainArgumentIndex(): ?int
    {
        return $this->domainArgumentIndex;
    }
}
