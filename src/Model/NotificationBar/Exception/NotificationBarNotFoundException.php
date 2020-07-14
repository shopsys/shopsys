<?php

declare(strict_types=1);

namespace App\Model\NotificationBar\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationBarNotFoundException extends NotFoundHttpException implements NotificationBarExceptionInterface
{
}
