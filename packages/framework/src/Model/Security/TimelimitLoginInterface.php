<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

interface TimelimitLoginInterface
{
    /**
     * @return \DateTime
     */
    public function getLastActivity();

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity);
}
