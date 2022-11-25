<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use DateTime;

interface TimelimitLoginInterface
{
    /**
     * @return \DateTime
     */
    public function getLastActivity();

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity(DateTime $lastActivity);
}
