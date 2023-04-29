<?php

declare(strict_types=1);

namespace Shopsys\S3Bridge\Component;

class S3Configuration
{
    /**
     * @param string $endpoint
     * @param string $region
     * @param string $accessKey
     * @param string $secret
     * @param string $bucketName
     * @param string $version
     */
    public function __construct(
        public string $endpoint,
        public string $region,
        public string $accessKey,
        public string $secret,
        public string $bucketName,
        public string $version,
    ) {
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->endpoint !== '';
    }
}
