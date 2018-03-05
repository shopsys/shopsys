<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Routing\RouteCollection;

class FriendlyUrlMatcher
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    private $friendlyUrlRepository;

    public function __construct(FriendlyUrlRepository $friendlyUrlRepository)
    {
        $this->friendlyUrlRepository = $friendlyUrlRepository;
    }

    /**
     * @param string $pathinfo
     * @param \Symfony\Component\Routing\RouteCollection $routeCollection
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function match($pathinfo, RouteCollection $routeCollection, DomainConfig $domainConfig)
    {
        $pathWithoutSlash = substr($pathinfo, 1);
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainConfig->getId(), $pathWithoutSlash);

        if ($friendlyUrl === null) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException();
        }

        $route = $routeCollection->get($friendlyUrl->getRouteName());
        if ($route === null) {
            throw new \Symfony\Component\Routing\Exception\ResourceNotFoundException();
        }

        $matchedParameters = $route->getDefaults();
        $matchedParameters['_route'] = $friendlyUrl->getRouteName();
        $matchedParameters['id'] = $friendlyUrl->getEntityId();

        if (!$friendlyUrl->isMain()) {
            $matchedParameters['_controller'] = 'FrameworkBundle:Redirect:redirect';
            $matchedParameters['route'] = $friendlyUrl->getRouteName();
            $matchedParameters['permanent'] = true;
        }

        return $matchedParameters;
    }
}
