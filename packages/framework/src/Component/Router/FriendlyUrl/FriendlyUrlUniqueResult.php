<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlUniqueResult
{
    /**
     * @var bool
     */
    private $unique;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    private $friendlyUrlForPersist;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null $friendlyUrl
     */
    public function __construct(bool $unique, FriendlyUrl $friendlyUrl = null)
    {
        $this->unique = $unique;
        $this->friendlyUrlForPersist = $friendlyUrl;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getFriendlyUrlForPersist(): ?\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
    {
        return $this->friendlyUrlForPersist;
    }
}
