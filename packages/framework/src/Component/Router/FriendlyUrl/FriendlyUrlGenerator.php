<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
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
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    protected $friendlyUrlRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider|null
     */
    protected ?FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider;

    /**
     * @var \Symfony\Contracts\Cache\CacheInterface|null
     */
    protected ?CacheInterface $mainFriendlyUrlSlugCache;

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider|null $friendlyUrlCacheKeyProvider
     * @param \Symfony\Contracts\Cache\CacheInterface|null $mainFriendlyUrlSlugCache
     */
    public function __construct(
        RequestContext $context,
        FriendlyUrlRepository $friendlyUrlRepository,
        ?FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider = null,
        ?CacheInterface $mainFriendlyUrlSlugCache = null
    ) {
        if ($mainFriendlyUrlSlugCache === null) {
            DeprecationHelper::trigger(
                'The argument "$mainFriendlyUrlSlugCache" is not provided by constructor in "%s". In the next major it will be required.',
                self::class
            );
        }

        parent::__construct(new RouteCollection(), $context, null);

        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->friendlyUrlCacheKeyProvider = $friendlyUrlCacheKeyProvider;
        $this->mainFriendlyUrlSlugCache = $mainFriendlyUrlSlugCache;
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routeCollection
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $routeName
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateFromRouteCollection(
        RouteCollection $routeCollection,
        DomainConfig $domainConfig,
        $routeName,
        array $parameters = [],
        $referenceType = self::ABSOLUTE_PATH
    ) {
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

        if ($this->mainFriendlyUrlSlugCache !== null) {
            $slug = $this->mainFriendlyUrlSlugCache->get(
                $this->friendlyUrlCacheKeyProvider->getMainFriendlyUrlSlugCacheKey(
                    $routeName,
                    $domainId,
                    (int)$entityId
                ),
                function () use ($domainId, $routeName, $entityId) {
                    return $this->getSlug($domainId, $routeName, $entityId);
                }
            );
        } else {
            $slug = $this->getSlug($domainId, $routeName, $entityId);
        }

        return $this->getGeneratedUrlBySlug($routeName, $route, $slug, $parameters, $referenceType);
    }

    /**
     * @param string $routeName
     * @param \Symfony\Component\Routing\Route $route
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function getGeneratedUrl($routeName, Route $route, FriendlyUrl $friendlyUrl, array $parameters, $referenceType)
    {
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
            $route->getSchemes()
        );
    }

    /**
     * @param string $routeName
     * @param \Symfony\Component\Routing\Route $route
     * @param string $slug
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function getGeneratedUrlBySlug(
        string $routeName,
        Route $route,
        string $slug,
        array $parameters,
        int $referenceType
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
            $route->getSchemes()
        );
    }

    /**
     * Not supported method
     *
     * @param mixed $routeName
     * @param mixed $parameters
     * @param mixed $referenceType
     */
    public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        throw new MethodGenerateIsNotSupportedException();
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    protected function getSlug(int $domainId, string $routeName, $entityId): string
    {
        try {
            $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl(
                $domainId,
                $routeName,
                $entityId
            );
            return $friendlyUrl->getSlug();
        } catch (FriendlyUrlNotFoundException $e) {
            $message = 'Unable to generate a URL for the named route "' . $routeName . '" as such route does not exist.';
            throw new RouteNotFoundException($message, 0, $e);
        }
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setFriendlyUrlCacheKeyProvider(FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider): void
    {
        if (
            $this->friendlyUrlCacheKeyProvider !== null
            && $this->friendlyUrlCacheKeyProvider !== $friendlyUrlCacheKeyProvider
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->friendlyUrlCacheKeyProvider !== null) {
            return;
        }

        DeprecationHelper::triggerSetterInjection(__METHOD__);

        $this->friendlyUrlCacheKeyProvider = $friendlyUrlCacheKeyProvider;
    }
}
