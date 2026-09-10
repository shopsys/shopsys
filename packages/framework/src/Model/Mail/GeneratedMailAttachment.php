<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

class GeneratedMailAttachment
{
    public function __construct(
        public readonly string $content,
        public readonly string $filename,
        public readonly string $contentType,
    ) {
    }
}
