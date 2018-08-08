<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

class TransMethodSpecification
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var int
     */
    private $messageIdArgumentIndex;

    /**
     * @var int|null
     */
    private $domainArgumentIndex;

    public function __construct(string $methodName, int $messageIdArgumentIndex = 0, ?int $domainArgumentIndex = null)
    {
        $this->methodName = $methodName;
        $this->messageIdArgumentIndex = $messageIdArgumentIndex;
        $this->domainArgumentIndex = $domainArgumentIndex;
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
