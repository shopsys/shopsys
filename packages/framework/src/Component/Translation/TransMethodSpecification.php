<?php

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
    public function __construct($methodName, $messageIdArgumentIndex = 0, $domainArgumentIndex = null)
    {
        $this->methodName = $methodName;
        $this->messageIdArgumentIndex = $messageIdArgumentIndex;
        $this->domainArgumentIndex = $domainArgumentIndex;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return int
     */
    public function getMessageIdArgumentIndex()
    {
        return $this->messageIdArgumentIndex;
    }

    /**
     * @return int|null
     */
    public function getDomainArgumentIndex()
    {
        return $this->domainArgumentIndex;
    }
}
