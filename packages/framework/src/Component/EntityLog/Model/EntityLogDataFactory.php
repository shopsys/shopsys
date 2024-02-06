<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

class EntityLogDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogData
     */
    protected function createInstance(): EntityLogData
    {
        return new EntityLogData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogData
     */
    public function create(): EntityLogData
    {
        return $this->createInstance();
    }
}
