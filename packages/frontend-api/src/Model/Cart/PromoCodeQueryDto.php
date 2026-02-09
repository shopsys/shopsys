<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final readonly class PromoCodeQueryDto
{
    public function __construct(
        public string $code,
        public string $type,
        public PriceInterface $discountPrice,
    ) {
    }
}
