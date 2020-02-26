<?php

declare(strict_types=1);


namespace App\Model\Product\Series\Category\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSeriesCategoryNotFoundException extends NotFoundHttpException implements ProductSeriesCategoryExceptionInterface
{
    public function __construct(int $productSeriesCategoryId, \Exception $previous = null)
    {
        $message = sprintf('ProductSeriesCategory with ID %d not found.', $productSeriesCategoryId);
        parent::__construct($message, $previous, 0);
    }
}