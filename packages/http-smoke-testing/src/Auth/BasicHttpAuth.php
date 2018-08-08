<?php

namespace Shopsys\HttpSmokeTesting\Auth;

use Symfony\Component\HttpFoundation\Request;

class BasicHttpAuth implements AuthInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var null|string
     */
    private $password;

    /**
     * @param string|null $password
     */
    public function __construct(string $username, ?string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticateRequest(Request $request): void
    {
        $request->server->set('PHP_AUTH_USER', $this->username);
        if ($this->password !== null) {
            $request->server->set('PHP_AUTH_PW', $this->password);
        }

        $request->headers->add($request->server->getHeaders());
    }
}
