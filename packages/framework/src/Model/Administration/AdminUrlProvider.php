<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administration;

class AdminUrlProvider
{
    public function __construct(
        protected readonly string $adminUrl,
    ) {
    }

    public function getAdminUrl(): string
    {
        return $this->adminUrl;
    }
}
