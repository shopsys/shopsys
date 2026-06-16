<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent;

class ConfirmationPageContent
{
    public function __construct(
        public readonly string $content,
        public readonly string $status,
    ) {
    }
}
