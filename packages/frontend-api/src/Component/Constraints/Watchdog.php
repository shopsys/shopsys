<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Watchdog extends Constraint
{
    public const INQUIRY_ERROR = 'bd70a05a-6bbb-4783-b9b2-c42824fa067d';
    public const MAIN_VARIANT_ERROR = 'a59f8293-2803-4571-b307-2b4ce72d39b4';
    public const PRODUCT_NOT_FOUND_ERROR = '8bbd03a5-48be-40fa-8c0f-5e3b1202adfd';

    public string $notAvailableInquiry = 'Watchdog is not available for product inquiry.';

    public string $notAvailableMainVariant = 'Watchdog is not available for product main variant.';

    public string $productNotFound = 'Product not found.';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::INQUIRY_ERROR => 'INQUIRY_ERROR',
        self::MAIN_VARIANT_ERROR => 'MAIN_VARIANT_ERROR',
        self::PRODUCT_NOT_FOUND_ERROR => 'PRODUCT_NOT_FOUND_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
