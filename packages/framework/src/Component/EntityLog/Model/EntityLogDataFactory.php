<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

class EntityLogDataFactory
{
    protected function createInstance(): EntityLogData
    {
        return new EntityLogData();
    }

    public function create(): EntityLogData
    {
        return $this->createInstance();
    }
}
