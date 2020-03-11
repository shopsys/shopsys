<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Exception;

use App\Model\Product\Series\ProductSeries;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductSeriesDomainNotFoundException extends NotFoundHttpException implements ProductSeriesException
{
    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @param \Exception|null $previous
     */
    public function __construct(ProductSeries $productSeries, ?\Exception $previous = null)
    {
        $message = sprintf('ProductSeriesDomain for ProductSeries ID %s not found.', $productSeries->getId());
        parent::__construct($message, $previous, 0);
    }
}
