<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FriendlyUrlGeneratorFacade
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly FriendlyUrlDataProviderRegistry $friendlyUrlDataProviderConfig,
    ) {
    }

    public function generateUrlsForSupportedEntities(OutputInterface $output): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $output->writeln(' Start of generating friendly urls for domain ' . $domainConfig->getUrl());

            $countOfCreatedUrls = $this->generateUrlsByDomainConfig($output, $domainConfig);

            $output->writeln(sprintf(
                ' End of generating friendly urls for domain %s (%d).',
                $domainConfig->getUrl(),
                $countOfCreatedUrls,
            ));
        }
    }

    protected function generateUrlsByDomainConfig(OutputInterface $output, DomainConfig $domainConfig): int
    {
        $totalCountOfCreatedUrls = 0;
        $friendlyUrlRouter = $this->domainRouterFactory->getFriendlyUrlRouter($domainConfig);

        foreach ($friendlyUrlRouter->getRouteCollection() as $routeName => $route) {
            $isMultidomain = $route->getOption('multidomain') ?? true;

            if ($isMultidomain === false) {
                continue;
            }

            $countOfCreatedUrls = $this->generateUrlsByRoute($domainConfig, $routeName);
            $totalCountOfCreatedUrls += $countOfCreatedUrls;

            $output->writeln(sprintf(
                '   -> route %s in %s (%d)',
                $routeName,
                $route->getDefault('_controller'),
                $countOfCreatedUrls,
            ));
        }

        return $totalCountOfCreatedUrls;
    }

    protected function generateUrlsByRoute(DomainConfig $domainConfig, string $routeName): int
    {
        $countOfCreatedUrls = 0;

        $friendlyUrlsData = $this->friendlyUrlDataProviderConfig->getFriendlyUrlDataByRouteAndDomain(
            $routeName,
            $domainConfig,
        );

        foreach ($friendlyUrlsData as $friendlyUrlData) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                $routeName,
                $friendlyUrlData->entityId,
                $friendlyUrlData->name,
                $domainConfig->getId(),
            );
            $countOfCreatedUrls++;
        }

        return $countOfCreatedUrls;
    }
}
