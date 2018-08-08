<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCompiler;

class FriendlyUrlGenerator extends BaseUrlGenerator
{
    /**
     * @var FriendlyUrlRepository
     */
    private $friendlyUrlRepository;

    public function __construct(
        RequestContext $context,
        FriendlyUrlRepository $friendlyUrlRepository
    ) {
        parent::__construct(new RouteCollection(), $context, null);

        $this->friendlyUrlRepository = $friendlyUrlRepository;
    }

    public function generateFromRouteCollection(
        RouteCollection $routeCollection,
        DomainConfig $domainConfig,
        string $routeName,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH
    ): string {
        $route = $routeCollection->get($routeName);
        if ($route === null) {
            $message = 'Unable to generate a URL for the named route "' . $routeName . '" as such route does not exist.';
            throw new \Symfony\Component\Routing\Exception\RouteNotFoundException($message);
        }
        if (!array_key_exists('id', $parameters)) {
            $message = 'Missing mandatory parameter "id" for route ' . $routeName . '.';
            throw new \Symfony\Component\Routing\Exception\MissingMandatoryParametersException($message);
        }
        $entityId = $parameters['id'];
        unset($parameters['id']);

        try {
            $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl(
                $domainConfig->getId(),
                $routeName,
                $entityId
            );
        } catch (\Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException $e) {
            $message = 'Unable to generate a URL for the named route "' . $routeName . '" as such route does not exist.';
            throw new \Symfony\Component\Routing\Exception\RouteNotFoundException($message, 0, $e);
        }

        return $this->getGeneratedUrl($routeName, $route, $friendlyUrl, $parameters, $referenceType);
    }

    public function getGeneratedUrl(string $routeName, Route $route, FriendlyUrl $friendlyUrl, array $parameters, string $referenceType): string
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
     * Not supported method
     */
    public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH): void
    {
        throw new \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\MethodGenerateIsNotSupportedException();
    }
}
