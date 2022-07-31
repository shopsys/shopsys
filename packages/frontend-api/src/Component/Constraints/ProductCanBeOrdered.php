<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ProductCanBeOrdered extends Constraint
{
    public const PRICES_DOES_NOT_MATCH_ERROR = 'c4ad85a0-9e32-4540-8491-9d899f3073bc';
    public const NO_SELLING_PRICE_ERROR = '3e834edb-2f0c-4424-a0c5-59ce34c5c2b1';
    public const PRODUCT_NOT_FOUND_ERROR = '1f2d316b-3edd-4869-bba6-b234856a7783';

    public string $pricesDoesNotMatchMessage = 'Price for product {{ uuid }} has changed';

    public string $noSellingPriceMessage = 'Product {{ uuid }} does not have any selling price';

    public string $productNotFoundMessage = 'Product {{ uuid }} no longer available or doesn\'t exist';

    protected static $errorNames = [
        self::PRICES_DOES_NOT_MATCH_ERROR => 'PRICES_DOES_NOT_MATCH_ERROR',
        self::NO_SELLING_PRICE_ERROR => 'NO_SELLING_PRICE_ERROR',
        self::PRODUCT_NOT_FOUND_ERROR => 'PRODUCT_NOT_FOUND_ERROR',
    ];
}
