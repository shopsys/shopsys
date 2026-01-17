<?php

declare(strict_types=1);

namespace Shopsys\S3Bridge\Component;

class S3Configuration
{
    public function __construct(
        public string $endpoint,
        public string $region,
        public string $accessKey,
        public string $secret,
        public string $bucketName,
        public string $version,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->endpoint !== '';
    }
}
