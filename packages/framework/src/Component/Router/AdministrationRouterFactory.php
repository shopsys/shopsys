<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AdministrationRouterFactory extends AbstractRouterFactory
{
    /**
     * @param string $routerConfiguration
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param string $routerConfigurationDir
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $cacheDir
     */
    public function __construct(
        protected readonly string $routerConfiguration,
        protected readonly Domain $domain,
        protected readonly string $routerConfigurationDir,
        RequestStack $requestStack,
        ContainerInterface $container,
        string $cacheDir,
    ) {
        parent::__construct($requestStack, $container, $cacheDir);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Router\AdministrationRouter
     */
    public function getAdministrationRouter(): AdministrationRouter
    {
        $domainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $requestContext = $this->getRequestContextByDomainConfig($domainConfig);
        $requestContext->setBaseUrl('/');
        $requestContext->setPathInfo(str_replace('/en', '', $requestContext->getPathInfo()));

        return new AdministrationRouter(
            $this->container,
            $this->routerConfiguration,
            $this->getRouterOptions(),
            $requestContext,
        );
    }

    //    /**
    //     * @return array
    //     */
    //    protected function getRouterOptions(): array
    //    {
    //        $options = ['resource_type' => 'glob'];
    //
    //        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
    //            $options['cache_dir'] = $this->cacheDir;
    //        }
    //
    //        return $options;
    //    }
}
