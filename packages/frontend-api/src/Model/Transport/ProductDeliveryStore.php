<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ProductDeliveryStore
{
    public function __construct(
        public readonly Store $store,
        public readonly ?DateTimeImmutable $expectedDeliveryDate,
    ) {
    }
}
