<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class McpChangeRequestStatusEnum extends AbstractEnum
{
    public const string PENDING = 'pending';
    public const string FAILED = 'failed';
    public const string INAPPLICABLE = 'inapplicable';
}
