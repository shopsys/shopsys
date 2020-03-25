<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UnitNotFoundException extends NotFoundHttpException implements UnitExceptionInterface
{
}
