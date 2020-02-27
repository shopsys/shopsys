<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSeriesCategoryNotFoundException extends NotFoundHttpException implements ProductSeriesCategoryExceptionInterface
{
    /**
     * @param int $productSeriesCategoryId
     */
    public function __construct(int $productSeriesCategoryId)
    {
        $message = sprintf('ProductSeriesCategory with ID %d not found.', $productSeriesCategoryId);
        parent::__construct($message);
    }
}
