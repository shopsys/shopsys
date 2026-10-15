<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Migration;

class ConversionContext
{
    public function __construct(
        public readonly string $profile,
        public readonly int $domainId,
        public readonly string $locale,
    ) {
    }
}
