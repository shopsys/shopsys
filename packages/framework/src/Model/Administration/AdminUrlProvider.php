<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administration;

class AdminUrlProvider
{
    /**
     * @param string $adminUrl
     */
    public function __construct(
        protected readonly string $adminUrl,
    ) {
    }

    /**
     * @return string
     */
    public function getAdminUrl(): string
    {
        return $this->adminUrl;
    }
}
