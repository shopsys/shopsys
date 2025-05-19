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
     * {@inheritdoc}
     */
    #[Override]
    public function push(Request $request)
    {
        $this->requestStack->push($request);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function pop(): ?Request
    {
        return $this->requestStack->pop();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getMainRequest(): ?Request
    {
        return $this->requestStack->getMainRequest();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParentRequest(): ?Request
    {
        return $this->requestStack->getParentRequest();
    }

    /**
     * {@inheritdoc}
     */
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
