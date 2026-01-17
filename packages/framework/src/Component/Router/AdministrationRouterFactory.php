<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AdministrationRouterFactory extends AbstractRouterFactory
{
    public function __construct(
        protected readonly string $routerConfiguration,
        protected readonly Domain $domain,
        RequestStack $requestStack,
        ContainerInterface $container,
        string $cacheDir,
    ) {
        parent::__construct($requestStack, $container, $cacheDir);
    }

    public function getAdministrationRouter(): AdministrationRouter
    {
        $domainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $requestContext = $this->getRequestContextByDomainConfig($domainConfig);
        $requestContext->setBaseUrl('/');

        if ($domainConfig->getPostfix() !== null) {
            $requestContext->setPathInfo(str_replace($domainConfig->getPostfix(), '', $requestContext->getPathInfo()));
        }

        return new AdministrationRouter(
            $this->container,
            $this->routerConfiguration,
            $this->getRouterOptions(),
            $requestContext,
        );
    }
}
