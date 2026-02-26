<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Override;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class AdministrationRouter implements RouterInterface, RequestMatcherInterface
{
    public function __construct(protected Router $router)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setContext(RequestContext $context): void
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function match(string $pathinfo): array
    {
        return $this->router->match($pathinfo);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function matchRequest(Request $request): array
    {
        return $this->router->matchRequest($request);
    }
}
