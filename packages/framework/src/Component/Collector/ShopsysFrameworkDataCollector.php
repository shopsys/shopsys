<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Collector;

use BadMethodCallException;
use PharIo\Version\Version;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\ShopsysFrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class ShopsysFrameworkDataCollector extends DataCollector
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface|null
     */
    protected $displayTimeZoneProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface|null $displayTimeZoneProvider
     */
    public function __construct(
        Domain $domain,
        ?DisplayTimeZoneProviderInterface $displayTimeZoneProvider = null
    ) {
        $this->domain = $domain;
        $this->displayTimeZoneProvider = $displayTimeZoneProvider;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setDisplayTimeZoneProvider(DisplayTimeZoneProviderInterface $displayTimeZoneProvider): void
    {
        if ($this->displayTimeZoneProvider !== null && $this->displayTimeZoneProvider !== $displayTimeZoneProvider) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }

        if ($this->displayTimeZoneProvider === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->displayTimeZoneProvider = $displayTimeZoneProvider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Exception $exception = null): void
    {
        $this->data = [
            'version' => ShopsysFrameworkBundle::VERSION,
            'docsVersion' => $this->resolveDocsVersion(ShopsysFrameworkBundle::VERSION),
            'domains' => $this->domain->getAll(),
            'currentDomainId' => $this->domain->getId(),
            'currentDomainName' => $this->domain->getName(),
            'currentDomainLocale' => $this->domain->getLocale(),
            'systemTimeZone' => date_default_timezone_get(),
            'displayTimeZone' => $this->displayTimeZoneProvider->getDisplayTimeZone()->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->data['version'];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getDomains(): array
    {
        return $this->data['domains'];
    }

    /**
     * @return int
     */
    public function getCurrentDomainId(): int
    {
        return $this->data['currentDomainId'];
    }

    /**
     * @return string
     */
    public function getCurrentDomainName(): string
    {
        return $this->data['currentDomainName'];
    }

    /**
     * @return string
     */
    public function getCurrentDomainLocale(): string
    {
        return $this->data['currentDomainLocale'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'shopsys_framework_core';
    }

    /**
     * @return string
     */
    public function getDocsVersion(): string
    {
        return $this->data['docsVersion'];
    }

    /**
     * @return string
     */
    public function getSystemTimeZone(): string
    {
        return $this->data['systemTimeZone'];
    }

    /**
     * @return string
     */
    public function getDisplayTimeZone(): string
    {
        return $this->data['displayTimeZone'];
    }

    /**
     * @param string $versionString
     * @return string
     */
    protected function resolveDocsVersion(string $versionString): string
    {
        $version = new Version($versionString);
        $versionMinorValue = (int)$version->getMinor()->getValue();

        if ($version->hasPreReleaseSuffix()
            && $version->getPreReleaseSuffix()->getValue() === 'dev'
            && (int)$version->getPatch()->getValue() === 0
            && $versionMinorValue > 0
        ) {
            $versionMinorValue--;
        }

        return sprintf('%d.%d', (int)$version->getMajor()->getValue(), $versionMinorValue);
    }
}
