<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use App\Model\CategorySeo\Exception\ReadyCategorySeoMixNotFoundException;
use App\Model\CategorySeo\ReadyCategorySeoMixRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher as BaseFriendlyUrlMatcher;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 */
class FriendlyUrlMatcher extends BaseFriendlyUrlMatcher
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixRepository $readyCategorySeoMixRepository
     */
    public function __construct(
        FriendlyUrlRepository $friendlyUrlRepository,
        private ReadyCategorySeoMixRepository $readyCategorySeoMixRepository,
    ) {
        parent::__construct($friendlyUrlRepository);
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
    private function getMatchedParametersForMainFrontCategorySeoFriendlyUrl(
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
    private function getMatchedParametersForNonMainFrontCategorySeoFriendlyUrl(
        FriendlyUrl $friendlyUrl,
        array $matchedParameters,
    ): array {
        $readyCategorySeoMixId = $friendlyUrl->getEntityId();

        $matchedParameters['_controller'] = 'FrameworkBundle:Redirect:redirect';

        // Both are needed
        $matchedParameters['route'] = $friendlyUrl->getRouteName();
        $matchedParameters['_route'] = $friendlyUrl->getRouteName();

        $matchedParameters['id'] = $readyCategorySeoMixId;
        $matchedParameters['permanent'] = true;

        return $matchedParameters;
    }
}
