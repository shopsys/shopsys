<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\RequestStack;

class ClientIpProvider
{
    protected const string UNKNOWN_CLIENT_IP = 'unknown';

    public function __construct(
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function getClientIp(): string
    {
        return $this->requestStack->getCurrentRequest()?->getClientIp() ?? static::UNKNOWN_CLIENT_IP;
    }
}
