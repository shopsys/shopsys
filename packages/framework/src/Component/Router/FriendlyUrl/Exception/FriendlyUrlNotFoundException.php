<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FriendlyUrlNotFoundException extends NotFoundHttpException implements FriendlyUrlException
{
}
