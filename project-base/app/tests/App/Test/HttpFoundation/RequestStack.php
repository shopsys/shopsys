<?php

declare(strict_types=1);

namespace Tests\App\Test\HttpFoundation;

use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack as BaseRequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class RequestStack extends BaseRequestStack
{
    private ?SessionInterface $session = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly BaseRequestStack $requestStack,
    ) {
    }

    #[Override]
    public function push(Request $request)
    {
        $this->requestStack->push($request);
    }

    #[Override]
    public function pop(): ?Request
    {
        return $this->requestStack->pop();
    }

    #[Override]
    public function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    #[Override]
    public function getMainRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    #[Override]
    public function getParentRequest(): ?Request
    {
        return $this->requestStack->getParentRequest();
    }

    #[Override]
    public function getSession(): SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            if (isset($this->session)) {
                return $this->session;
            }

            $sessionSavePath = $this->container->getParameter('session.save_path');
            $sessionStorage = new MockFileSessionStorage($sessionSavePath);
            $session = new Session($sessionStorage);
            $this->session = $session;

            return $this->session;
        }
    }
}
