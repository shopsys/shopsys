<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage;

class PaymentContentPage
{
    public function __construct(
        public readonly string $content,
        public readonly string $status,
    ) {
    }
}
