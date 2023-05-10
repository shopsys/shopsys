<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ProductInOrder extends Constraint
{
    public const NO_PRODUCT_IN_ORDER_ERROR = '2e34acd7-7266-4057-ab1a-4ee997f3d2a5';

    public string $noProductInOrderMessage = 'There are no products in the cart';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::NO_PRODUCT_IN_ORDER_ERROR => 'NO_PRODUCT_IN_ORDER_ERROR',
    ];

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
