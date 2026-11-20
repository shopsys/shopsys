<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

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
     * @var array{subject: string, subjectParameters: array<string, string|int>, subjectUrl: string|null, fields: array<int, array{label: string, labelParameters: array<string, string|int>, before: string|null, after: string|null}>}
     */
    public $change;

    /**
     * @var \DateTimeImmutable
     */
    public $createdAt;
}
