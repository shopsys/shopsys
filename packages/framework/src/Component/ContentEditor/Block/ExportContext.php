<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block;

class ExportContext
{
    public function __construct(
        public readonly int $domainId,
        public readonly string $locale,
    ) {
    }
}
