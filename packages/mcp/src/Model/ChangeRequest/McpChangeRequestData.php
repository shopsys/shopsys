<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

/**
 * @phpstan-import-type McpChangeArray from \Shopsys\McpBundle\Component\WriteTool\McpChange
 */
class McpChangeRequestData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public $administrator;

    /**
     * @var string
     */
    public $toolName;

    /**
     * @var class-string
     */
    public $argumentsClass;

    /**
     * @var array<string, mixed>
     */
    public $arguments;

    /**
     * @var McpChangeArray
     */
    public $change;

    /**
     * @var \DateTimeImmutable
     */
    public $createdAt;
}
