<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Collector;

use Override;
use PharIo\Version\Version;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\ShopsysFrameworkBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class ShopsysFrameworkDataCollector extends DataCollector
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface $contextResolver
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade $administratorLocalizationFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data = [
            'version' => ShopsysFrameworkBundle::VERSION,
            'docsVersion' => $this->resolveDocsVersion(ShopsysFrameworkBundle::VERSION),
            'domains' => $this->domain->getAll(),
            'systemTimeZone' => date_default_timezone_get(),
        ];

        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            $this->data['inAdmin'] = true;
            $this->data['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName();
            $this->data['adminLocale'] = $this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault();
            $this->data['currentDomainId'] = 0;
        } else {
            $this->data['inAdmin'] = false;
            $this->data['currentDomainId'] = $this->domain->getId();
            $this->data['currentDomainName'] = $this->domain->getName();
            $this->data['currentDomainLocale'] = $this->domain->getLocale();
            $this->data['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId())->getName();
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
     * @return bool
     */
    public function isInAdmin(): bool
    {
        return $this->data['inAdmin'];
    }

    /**
     * @return string
     */
    public function getAdminLocale(): string
    {
        return $this->data['adminLocale'];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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
