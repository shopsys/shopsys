<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ProductInOrder extends Constraint
{
    public const NO_PRODUCT_IN_ORDER_ERROR = '2e34acd7-7266-4057-ab1a-4ee997f3d2a5';

    public string $noProductInOrderMessage = 'There are no products in the cart';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::NO_PRODUCT_IN_ORDER_ERROR => 'NO_PRODUCT_IN_ORDER_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
