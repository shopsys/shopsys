<?php

namespace Shopsys\FrameworkBundle\Model\Security;

interface TimelimitLoginInterface
{
    public function getLastActivity(): \DateTime;
    
    public function setLastActivity(\DateTime $lastActivity): void;
}
