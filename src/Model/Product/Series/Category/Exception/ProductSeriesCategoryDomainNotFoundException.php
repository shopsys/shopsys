<?php

declare(strict_types=1);


namespace App\Model\Product\Series\Category\Exception;

use Exception;
use Throwable;

class ProductSeriesCategoryDomainNotFoundException extends Exception implements ProductSeriesCategoryExceptionInterface
{
    /**
     * @param int|null $productSeriesCategoryId
     * @param int $domainId
     * @param \Throwable|null $previous
     */
    public function __construct(?int $productSeriesCategoryId, int $domainId, Throwable $previous = null)
    {
        $description = $productSeriesCategoryId !== null ? sprintf('with ID %d', $productSeriesCategoryId) : 'without ID';
        $message = sprintf('ProductSeriesCategoryDomain for product series category %s and domain ID %d not found.', $description, $domainId);

        parent::__construct($message, 0, $previous);
    }
}