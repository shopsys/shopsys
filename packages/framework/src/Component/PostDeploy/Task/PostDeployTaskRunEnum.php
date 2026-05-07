<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class PostDeployTaskRunEnum extends AbstractEnum
{
    public const string ONE_TIME = 'one_time';
    public const string ALWAYS = 'always';
    public const string NEVER = 'never';
}
