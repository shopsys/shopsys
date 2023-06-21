<?php

declare(strict_types=1);

namespace App\Model\Stock\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StockNotFoundException extends NotFoundHttpException implements StockException
{
}
