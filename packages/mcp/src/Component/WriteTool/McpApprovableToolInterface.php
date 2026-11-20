<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\WriteTool;

/**
 * @template TArguments of object
 */
interface McpApprovableToolInterface
{
    public const string TAG = 'shopsys.mcp.approvable_tool';

    public static function getToolName(): string;

    /**
     * @param TArguments $arguments
     */
    public function describeChange(object $arguments): McpChange;

    /**
     * @param TArguments $arguments
     */
    public function apply(object $arguments): void;
}
