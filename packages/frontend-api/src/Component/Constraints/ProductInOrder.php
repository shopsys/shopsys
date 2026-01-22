<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class ProductInOrder extends Constraint
{
    public const NO_PRODUCT_IN_ORDER_ERROR = '2e34acd7-7266-4057-ab1a-4ee997f3d2a5';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::NO_PRODUCT_IN_ORDER_ERROR => 'NO_PRODUCT_IN_ORDER_ERROR',
    ];

    /**
     * @param string $noProductInOrderMessage
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $noProductInOrderMessage = 'There are no products in the cart',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
