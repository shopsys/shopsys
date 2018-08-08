<?php

namespace Shopsys\FrameworkBundle\Model\Security;

interface TimelimitLoginInterface
{
    public function getLastActivity(): \DateTime;

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity);
}
