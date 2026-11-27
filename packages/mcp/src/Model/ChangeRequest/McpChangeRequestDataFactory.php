<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

class McpChangeRequestDataFactory
{
    protected function createInstance(): McpChangeRequestData
    {
        return new McpChangeRequestData();
    }

    public function create(): McpChangeRequestData
    {
        return $this->createInstance();
    }
}
