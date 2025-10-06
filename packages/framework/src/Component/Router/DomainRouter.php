<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Override;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class DomainRouter extends ChainRouter
{
    protected bool $freeze = false;

    public const int SLUG = 10;

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     * @param \Symfony\Component\Routing\RouterInterface $basicRouter
     * @param \Symfony\Component\Routing\RouterInterface $localizedRouter
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter $friendlyUrlRouter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     * @param \Psr\Log\LoggerInterface|null $logger
     * @throws \Shopsys\FrameworkBundle\Component\Router\Exception\NotSupportedException
     */
    public function __construct(
        RequestContext $context,
        RouterInterface $basicRouter,
        RouterInterface $localizedRouter,
        protected readonly FriendlyUrlRouter $friendlyUrlRouter,
        protected readonly DomainConfig $domainConfig,
        protected readonly TransformStringHelper $transformStringHelper,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);

        $this->setContext($context);
        $this->freeze = true;

        $this->add($basicRouter, 30);
        $this->add($localizedRouter, 20);
        $this->add($friendlyUrlRouter, 10);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateByFriendlyUrl(
        FriendlyUrl $friendlyUrl,
        array $parameters = [],
        $referenceType = self::ABSOLUTE_PATH,
    ) {
        return $this->friendlyUrlRouter->generateByFriendlyUrl($friendlyUrl, $parameters, $referenceType);
    }

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     */
    #[Override]
    public function setContext(RequestContext $context): void
    {
        if ($this->freeze) {
            $message = 'Set context is not supported in chain DomainRouter';

            throw new NotSupportedException($message);
        }

        parent::setContext($context);
    }

    /**
     * @param string $pathinfo
     * @return array
     */
    #[Override]
    public function match(string $pathinfo): array
    {
        $pathinfo = $this->rebuildPathInfoByCurrentDomainConfig($pathinfo);

        return parent::match($pathinfo);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    #[Override]
    public function matchRequest(Request $request): array
    {
        $request = $this->rebuildRequestByCurrentDomainConfig($request->getPathInfo());

        return parent::matchRequest($request);
    }

    /**
     * @param string $pathinfo
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function rebuildRequestByCurrentDomainConfig(string $pathinfo): Request
    {
        $pathinfo = $this->rebuildPathInfoByCurrentDomainConfig($pathinfo);
        $context = $this->getContext();
        $uri = $pathinfo;
        $server = [];

        if ($context->getBaseUrl()) {
            $uri = $context->getBaseUrl() . $pathinfo;
            $server['SCRIPT_FILENAME'] = $context->getBaseUrl();
            $server['PHP_SELF'] = $context->getBaseUrl();
        }
        $host = $context->getHost() ?: 'localhost';

        if ($context->getScheme() === 'https' && $context->getHttpsPort() !== 443) {
            $host .= ':' . $context->getHttpsPort();
        }

        if ($context->getScheme() === 'http' && $context->getHttpPort() !== 80) {
            $host .= ':' . $context->getHttpPort();
        }
        $uri = $context->getScheme() . '://' . $host . $uri . '?' . $context->getQueryString();


        return Request::create($uri, $context->getMethod(), $context->getParameters(), [], [], $server);
    }

    /**
     * @param string $pathinfo
     * @return string
     */
    protected function rebuildPathInfoByCurrentDomainConfig(string $pathinfo): string
    {
        return $this->filterByDomainConfig($pathinfo, $this->domainConfig);
    }

    /**
     * @param string $url
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public static function filterByDomainConfig(string $url, DomainConfig $domainConfig): string
    {
        $urlComponents = parse_url($url);
        $domainConfigUrlComponents = parse_url($domainConfig->getUrl());
        $isRelative = array_key_exists('host', $urlComponents) === false;

        if (!$isRelative && $urlComponents['host'] !== $domainConfigUrlComponents['host']) {
            return $url;
        }

        if (array_key_exists('path', $domainConfigUrlComponents) === false) {
            return $url;
        }

        $urlPathQuoted = preg_quote($domainConfigUrlComponents['path'], '/');
        $domainConfigPrefixPattern = sprintf('/(^%s$)|(^%s\/)/', $urlPathQuoted, $urlPathQuoted);

        if ($isRelative) {
            return preg_replace($domainConfigPrefixPattern, '/', $url, 1);
        }

        if (!array_key_exists('path', $urlComponents) || !preg_match($domainConfigPrefixPattern, $urlComponents['path'])) {
            return $url;
        }

        $domainUrl = $domainConfig->getUrl();
        $baseUrl = str_replace($domainConfigUrlComponents['path'], '', $domainConfig->getUrl());

        return str_replace($domainUrl, $baseUrl, $url);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    #[Override]
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        $url = parent::generate($name, $parameters, $referenceType === static::SLUG ? UrlGeneratorInterface::ABSOLUTE_PATH : $referenceType);

        if ($referenceType === static::SLUG && $this->domainConfig->getPostfix() !== null) {
            $url = $this->transformStringHelper->removeStringFromStart($url, $this->domainConfig->getPostfix());
        }

        return $url;
    }
}
