<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim;

class ConvertimConfig
{
    /**
     * @param bool $isEnabled
     * @param string $authorizationHeader
     * @param string $projectUuid
     * @param bool $isProductionMode
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(
        public readonly bool $isEnabled,
        public readonly string $authorizationHeader,
        public readonly string $projectUuid,
        public readonly bool $isProductionMode,
        public readonly string $clientId,
        public readonly string $clientSecret,
    ) {
    }
}
