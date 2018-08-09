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

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function getMessageIdArgumentIndex()
    {
        return $this->messageIdArgumentIndex;
    }

    public function getDomainArgumentIndex()
    {
        return $this->domainArgumentIndex;
    }
}
