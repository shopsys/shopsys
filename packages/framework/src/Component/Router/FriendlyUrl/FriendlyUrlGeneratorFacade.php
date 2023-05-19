<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Console\Output\OutputInterface;

class FriendlyUrlGeneratorFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlDataProviderRegistry $friendlyUrlDataProviderConfig
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly FriendlyUrlDataProviderRegistry $friendlyUrlDataProviderConfig,
    ) {
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function generateUrlsForSupportedEntities(OutputInterface $output)
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

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return int
     */
    protected function generateUrlsByDomainConfig(OutputInterface $output, DomainConfig $domainConfig)
    {
        $totalCountOfCreatedUrls = 0;
        $friendlyUrlRouter = $this->domainRouterFactory->getFriendlyUrlRouter($domainConfig);

        foreach ($friendlyUrlRouter->getRouteCollection() as $routeName => $route) {
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $routeName
     * @return int
     */
    protected function generateUrlsByRoute(DomainConfig $domainConfig, $routeName)
    {
        $countOfCreatedUrls = 0;

        $friendlyUrlsData = $this->friendlyUrlDataProviderConfig->getFriendlyUrlDataByRouteAndDomain(
            $routeName,
            $domainConfig,
        );

        foreach ($friendlyUrlsData as $friendlyUrlData) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                $routeName,
                $friendlyUrlData->id,
                $friendlyUrlData->name,
                $domainConfig->getId(),
            );
            $countOfCreatedUrls++;
        }

        return $countOfCreatedUrls;
    }
}
