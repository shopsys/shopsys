<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final readonly class PromoCodeQueryDto
{
    /**
     * @param string $code
     * @param string $type
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $discountPrice
     */
    public function __construct(
        public string $code,
        public string $type,
        public PriceInterface $discountPrice,
    ) {
    }
}
