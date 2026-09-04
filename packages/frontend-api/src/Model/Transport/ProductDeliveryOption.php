<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class ProductDeliveryOption
{
    public function __construct(
        public readonly Transport $transport,
        public readonly PriceInterface $price,
        public readonly ?DateTimeImmutable $expectedDeliveryDate,
    ) {
    }
}
