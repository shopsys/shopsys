<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Auth;

use Override;
use Symfony\Component\HttpFoundation\Request;

class BasicHttpAuth implements AuthInterface
{
    public function __construct(private string $username, private ?string $password = null)
    {
    }

    #[Override]
    public function authenticateRequest(Request $request): void
    {
        $request->server->set('PHP_AUTH_USER', $this->username);

        if ($this->password !== null) {
            $request->server->set('PHP_AUTH_PW', $this->password);
        }

        $request->headers->add($request->server->getHeaders());
    }
}
