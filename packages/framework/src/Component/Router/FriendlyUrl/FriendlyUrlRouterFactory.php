<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;

class FriendlyUrlRouterFactory
{
    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    protected $configLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    protected $friendlyUrlRepository;

    /**
     * @var string
     */
    protected $friendlyUrlRouterResourceFilepath;

    /**
     * @param mixed $friendlyUrlRouterResourceFilepath
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     */
    public function __construct(
        $friendlyUrlRouterResourceFilepath,
        LoaderInterface $configLoader,
        FriendlyUrlRepository $friendlyUrlRepository
    ) {
        $this->friendlyUrlRouterResourceFilepath = $friendlyUrlRouterResourceFilepath;
        $this->configLoader = $configLoader;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Symfony\Component\Routing\RequestContext $context
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter
     */
    public function createRouter(DomainConfig $domainConfig, RequestContext $context)
    {
        return new FriendlyUrlRouter(
            $context,
            $this->configLoader,
            new FriendlyUrlGenerator($context, $this->friendlyUrlRepository),
            new FriendlyUrlMatcher($this->friendlyUrlRepository),
            $domainConfig,
            $this->friendlyUrlRouterResourceFilepath
        );
    }
}
