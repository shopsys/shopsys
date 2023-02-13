<?php

declare(strict_types=1);

namespace Tests\App\Test\HttpFoundation;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack as BaseRequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class RequestStack extends BaseRequestStack
{
    private ?SessionInterface $session;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly BaseRequestStack $requestStack,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function push(Request $request)
    {
        $this->requestStack->push($request);
    }

    /**
     * @inheritDoc
     */
    public function pop()
    {
        return $this->requestStack->pop();
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @inheritDoc
     */
    public function getMainRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * @inheritDoc
     */
    public function getMasterRequest()
    {
        return $this->requestStack->getMasterRequest();
    }

    /**
     * @inheritDoc
     */
    public function getParentRequest()
    {
        return $this->requestStack->getParentRequest();
    }

    /**
     * @inheritDoc
     */
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
