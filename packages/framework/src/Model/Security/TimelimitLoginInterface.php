<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

interface TimelimitLoginInterface
{
    /**
     * @return \DateTimeImmutable
     */
    public function getLastActivity();

    /**
     * @param \DateTimeImmutable $lastActivity
     */
    public function setLastActivity($lastActivity): void;
}
