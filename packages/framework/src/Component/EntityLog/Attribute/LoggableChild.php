<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class LoggableChild extends Loggable
{
}
