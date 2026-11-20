<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class McpChangeRequestNotFoundException extends NotFoundHttpException
{
}
