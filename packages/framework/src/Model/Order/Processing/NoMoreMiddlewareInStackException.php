<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use RuntimeException;

class NoMoreMiddlewareInStackException extends RuntimeException
{
}
