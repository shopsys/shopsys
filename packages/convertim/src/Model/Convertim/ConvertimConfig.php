<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim;

class ConvertimConfig
{
    /**
     * @param bool $isEnabled
     * @param string $authorizationHeader
     * @param string $projectUuid
     */
    public function __construct(
        public readonly bool $isEnabled,
        public readonly string $authorizationHeader,
        public readonly string $projectUuid,
    ) {
    }
}
