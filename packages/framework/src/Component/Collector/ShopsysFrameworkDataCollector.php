<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Collector;

use Override;
use PharIo\Version\Version;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ConsoleContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
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
            'docsUrl' => sprintf('https://docs.shopsys.com/en/%s/', $this->resolveDocsVersion(ShopsysFrameworkBundle::VERSION)),
            'domains' => $this->domain->getAll(),
            'systemTimeZone' => date_default_timezone_get(),
            'inAdmin' => false,
            'currentContext' => $this->resolveCurrentContext(),
            'currentDomainId' => 0,
            'currentDomainName' => 'N/A',
            'currentDomainLocale' => 'N/A',
            'adminLocale' => 'N/A',
            'displayTimeZone' => 'N/A',
        ];

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            $this->data['currentDomainId'] = $this->domain->getId();
            $this->data['currentDomainName'] = $this->domain->getName();
            $this->data['currentDomainLocale'] = $this->domain->getLocale();
            $this->data['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId())->getName();
        } elseif ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            $this->data['inAdmin'] = true;
            $this->data['adminLocale'] = $this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault();
            $this->data['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName();
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

    public function getCurrentDomainId(): int
    {
        return $this->data['currentDomainId'];
    }

    public function getCurrentDomainName(): string
    {
        return $this->data['currentDomainName'];
    }

    public function getCurrentDomainLocale(): string
    {
        return $this->data['currentDomainLocale'];
    }

    public function isInAdmin(): bool
    {
        return $this->data['inAdmin'];
    }

    public function getCurrentContext(): string
    {
        return $this->data['currentContext'];
    }

    public function getAdminLocale(): string
    {
        return $this->data['adminLocale'];
    }

    protected function resolveCurrentContext(): string
    {
        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            return 'Administration';
        }

        if ($this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return 'Frontend API';
        }

        if ($this->contextResolver->isCurrentContext(ConsoleContext::class)) {
            return 'Console';
        }

        return 'Unknown';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'shopsys_framework_core';
    }

    public function getDocsUrl(): string
    {
        return $this->data['docsUrl'];
    }

    public function getSystemTimeZone(): string
    {
        return $this->data['systemTimeZone'];
    }

    public function getDisplayTimeZone(): string
    {
        return $this->data['displayTimeZone'];
    }

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
