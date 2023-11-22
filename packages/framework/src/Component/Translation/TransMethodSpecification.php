<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

class TransMethodSpecification
{
    protected string $methodName;

    protected int $messageIdArgumentIndex;

    protected ?int $domainArgumentIndex = null;

    /**
     * @param string $methodName
     * @param int $messageIdArgumentIndex
     * @param int|null $domainArgumentIndex
     */
    public function __construct(string $methodName, int $messageIdArgumentIndex = 0, ?int $domainArgumentIndex = null)
    {
        $this->methodName = $methodName;
        $this->messageIdArgumentIndex = $messageIdArgumentIndex;
        $this->domainArgumentIndex = $domainArgumentIndex;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return int
     */
    public function getMessageIdArgumentIndex(): int
    {
        return $this->messageIdArgumentIndex;
    }

    /**
     * @return int|null
     */
    public function getDomainArgumentIndex(): ?int
    {
        return $this->domainArgumentIndex;
    }
}
