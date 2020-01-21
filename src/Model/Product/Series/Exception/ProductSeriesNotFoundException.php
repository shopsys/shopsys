<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSeriesNotFoundException extends NotFoundHttpException implements ProductSeriesException
{
}
