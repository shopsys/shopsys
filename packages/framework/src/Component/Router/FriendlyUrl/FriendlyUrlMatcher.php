<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

class FriendlyUrlMatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     */
    public function __construct(
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
    ) {
    }

    /**
     * @param string $pathinfo
     * @param \Symfony\Component\Routing\RouteCollection $routeCollection
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function match(string $pathinfo, RouteCollection $routeCollection, DomainConfig $domainConfig): array
    {
        $pathWithoutSlash = substr($pathinfo, 1);
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainConfig->getId(), $pathWithoutSlash);

        if ($friendlyUrl === null) {
            throw new ResourceNotFoundException();
        }

        $matchedParameters = [];

        if ($friendlyUrl->getRedirectTo() !== null) {
            $matchedParameters['_route'] = $friendlyUrl->getRouteName();
            $matchedParameters['_controller'] = 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction';
            $matchedParameters['path'] = $friendlyUrl->getRedirectTo();
            $matchedParameters['permanent'] = $friendlyUrl->getRedirectCode() !== 302;
            $matchedParameters['id'] = $friendlyUrl->getEntityId();

            return $matchedParameters;
        }

        $route = $routeCollection->get($friendlyUrl->getRouteName());

        if ($route === null) {
            throw new ResourceNotFoundException();
        }

        $matchedParameters = $route->getDefaults();

        if ($friendlyUrl->getRouteName() === 'front_category_seo' && $friendlyUrl->isMain() === false) {
            return $this->getMatchedParametersForNonMainFrontCategorySeoFriendlyUrl($friendlyUrl, $matchedParameters);
        }

        if ($friendlyUrl->getRouteName() === 'front_category_seo') {
            return $this->getMatchedParametersForMainFrontCategorySeoFriendlyUrl($friendlyUrl, $matchedParameters);
        }

        $matchedParameters['_route'] = $friendlyUrl->getRouteName();
        $matchedParameters['id'] = $friendlyUrl->getEntityId();

        if (!$friendlyUrl->isMain()) {
            $matchedParameters['_controller'] = 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction';
            $matchedParameters['route'] = $friendlyUrl->getRouteName();
            $matchedParameters['permanent'] = true;
        }

        return $matchedParameters;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $matchedParameters
     * @return array
     */
    protected function getMatchedParametersForMainFrontCategorySeoFriendlyUrl(
        FriendlyUrl $friendlyUrl,
        array $matchedParameters,
    ): array {
        $readyCategorySeoMixId = $friendlyUrl->getEntityId();
        $readyCategorySeoMix = $this->readyCategorySeoMixRepository->findById($readyCategorySeoMixId);

        if ($readyCategorySeoMix === null) {
            throw new ReadyCategorySeoMixNotFoundException(sprintf('ReadyCategorySeoMix with ID %s not found', $readyCategorySeoMixId));
        }

        $matchedParameters['_route'] = 'front_product_list';
        $matchedParameters['id'] = $readyCategorySeoMix->getCategory()->getId();
        $matchedParameters['readyCategorySeoMixId'] = $readyCategorySeoMixId;

        return $matchedParameters;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $matchedParameters
     * @return array
     */
    protected function getMatchedParametersForNonMainFrontCategorySeoFriendlyUrl(
        FriendlyUrl $friendlyUrl,
        array $matchedParameters,
    ): array {
        $readyCategorySeoMixId = $friendlyUrl->getEntityId();

        $matchedParameters['_controller'] = 'FrameworkBundle:Redirect:redirect';

        // Both are necessary
        $matchedParameters['route'] = $friendlyUrl->getRouteName();
        $matchedParameters['_route'] = $friendlyUrl->getRouteName();

        $matchedParameters['id'] = $readyCategorySeoMixId;
        $matchedParameters['permanent'] = true;

        return $matchedParameters;
    }
}
