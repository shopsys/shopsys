<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClosedDayNotFoundException extends NotFoundHttpException
{
}
