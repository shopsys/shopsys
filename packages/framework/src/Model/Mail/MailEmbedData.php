<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

class MailEmbedData
{
    public function __construct(
        public readonly string $embed,
        public readonly string $fileName,
        public readonly string $contentType,
    ) {
    }
}
