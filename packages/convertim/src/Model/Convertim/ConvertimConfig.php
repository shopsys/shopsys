<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim;

class ConvertimConfig
{
    /**
     * @param bool $isEnabled
     * @param string $authorizationHeader
     */
    public function __construct(
        protected readonly bool $isEnabled,
        protected readonly string $authorizationHeader,
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        return $this->authorizationHeader;
    }
}
