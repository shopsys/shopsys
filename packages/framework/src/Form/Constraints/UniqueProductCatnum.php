<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Validator\Constraint;

class UniqueProductCatnum extends Constraint
{
    /**
     * @param string $message
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     * @param array|null $groups
     * @param mixed $payload
     */
    public function __construct(
        public string $message = 'Product with entered catalog number already exists',
        public ?Product $product = null,
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
        return self::PROPERTY_CONSTRAINT;
    }
}
