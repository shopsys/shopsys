<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlUniqueResult
{
    protected ?FriendlyUrl $friendlyUrlForPersist;

    public function __construct(protected bool $unique, ?FriendlyUrl $friendlyUrl = null)
    {
        $this->friendlyUrlForPersist = $friendlyUrl;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getFriendlyUrlForPersist(): ?FriendlyUrl
    {
        return $this->friendlyUrlForPersist;
    }
}
