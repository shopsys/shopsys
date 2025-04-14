<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage;

class PaymentContentPage
{
    /**
     * @param string $content
     * @param string $status
     */
    public function __construct(
        public readonly string $content,
        public readonly string $status,
    ) {
    }
}
