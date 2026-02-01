<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\MethodGenerateIsNotSupportedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlGenerator extends BaseUrlGenerator
{
    public function __construct(
        RequestContext $context,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider,
        protected readonly CacheInterface $mainFriendlyUrlSlugCache,
    ) {
        parent::__construct(new RouteCollection(), $context, null);
    }

    public function generateFromRouteCollection(
        RouteCollection $routeCollection,
        DomainConfig $domainConfig,
        string $routeName,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
    ): string {
        $route = $routeCollection->get($routeName);

        if ($route === null) {
            $message = 'Unable to generate a URL for the named route "' . $routeName . '" as such route does not exist.';

            throw new RouteNotFoundException($message);
        }

        if (!array_key_exists('id', $parameters)) {
            $message = 'Missing mandatory parameter "id" for route ' . $routeName . '.';

            throw new MissingMandatoryParametersException($message);
        }
        $entityId = $parameters['id'];
        unset($parameters['id']);

        $domainId = $domainConfig->getId();

        $slug = $this->mainFriendlyUrlSlugCache->get(
            $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(
                $routeName,
                $domainId,
                (int)$entityId,
            ),
            function () use ($domainId, $routeName, $entityId) {
                return $this->getSlug($domainId, $routeName, $entityId);
            },
        );

        return $this->getGeneratedUrlBySlug($routeName, $route, $slug, $parameters, $referenceType);
    }

    public function getGeneratedUrl(
        string $routeName,
        Route $route,
        FriendlyUrl $friendlyUrl,
        array $parameters,
        int $referenceType,
    ): string {
        $compiledRoute = RouteCompiler::compile($route);

        $tokens = [
            [
                0 => 'text',
                1 => '/' . $friendlyUrl->getSlug(),
            ],
        ];

        return $this->doGenerate(
            $compiledRoute->getVariables(),
            $route->getDefaults(),
            $route->getRequirements(),
            $tokens,
            $parameters,
            $routeName,
            $referenceType,
            $compiledRoute->getHostTokens(),
            $route->getSchemes(),
        );
    }

    public function getGeneratedUrlBySlug(
        string $routeName,
        Route $route,
        string $slug,
        array $parameters,
        int $referenceType,
    ): string {
        $compiledRoute = RouteCompiler::compile($route);

        $tokens = [
            [
                0 => 'text',
                1 => '/' . $slug,
            ],
        ];

        return $this->doGenerate(
            $compiledRoute->getVariables(),
            $route->getDefaults(),
            $route->getRequirements(),
            $tokens,
            $parameters,
            $routeName,
            $referenceType,
            $compiledRoute->getHostTokens(),
            $route->getSchemes(),
        );
    }

    /**
     * Not supported method
     */
    #[Override]
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        throw new MethodGenerateIsNotSupportedException();
    }

    protected function getSlug(int $domainId, string $routeName, int $entityId): string
    {
        try {
            $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl(
                $domainId,
                $routeName,
                $entityId,
            );

            return $friendlyUrl->getSlug();
        } catch (FriendlyUrlNotFoundException $e) {
            $message = 'Unable to generate a URL for the named route "' . $routeName . '" as such route does not exist.';

            throw new RouteNotFoundException($message, 0, $e);
        }
    }
}
