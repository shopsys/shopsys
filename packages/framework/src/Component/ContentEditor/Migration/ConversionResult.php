<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Migration;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;

class ConversionResult
{
    /**
     * @param string[] $notes what was dropped or changed by the conversion
     */
    public function __construct(
        public readonly ContentDocument $document,
        public readonly array $notes,
    ) {
    }
}
