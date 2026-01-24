<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

class AbstractRouterFactory
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ContainerInterface $container,
        protected readonly string $cacheDir,
    ) {
    }

    protected function getRequestContextByDomainConfig(
        DomainConfig $domainConfig,
    ): RequestContext {
        $urlComponents = parse_url($domainConfig->getUrl());
        $requestContext = new RequestContext();
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null) {
            $requestContext->fromRequest($request);
        }

        if (array_key_exists('path', $urlComponents)) {
            $requestContext->setBaseUrl($urlComponents['path']);
        }

        $requestContext->setScheme($urlComponents['scheme']);
        $requestContext->setHost($urlComponents['host']);

        if (array_key_exists('port', $urlComponents)) {
            if ($urlComponents['scheme'] === 'http') {
                $requestContext->setHttpPort($urlComponents['port']);
            } elseif ($urlComponents['scheme'] === 'https') {
                $requestContext->setHttpsPort($urlComponents['port']);
            }
        }

        return $requestContext;
    }

    protected function getRouterOptions(): array
    {
        $options = ['resource_type' => 'service'];

        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
            $options['cache_dir'] = $this->cacheDir;
        }

        return $options;
    }
}
