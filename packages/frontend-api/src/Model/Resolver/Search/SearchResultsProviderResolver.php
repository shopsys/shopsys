<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Search;

use Shopsys\FrontendApiBundle\Model\Resolver\Search\Exception\NoSearchResultsProviderEnabledOnDomainException;
use Shopsys\FrontendApiBundle\Model\Resolver\Search\Exception\SearchResultsProviderWithSamePriorityAlreadyExistsException;
use Webmozart\Assert\Assert;

abstract class SearchResultsProviderResolver
{
    /**
     * @var array<int, string>
     */
    protected array $searchResultsProvidersServiceIdByPriority = [];

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface[] $searchResultsProviders
     */
    public function __construct(
        protected readonly iterable $searchResultsProviders,
    ) {
    }

    /**
     * @return string
     */
    abstract protected function getSearchResultsProviderInterface(): string;

    /**
     * @param int $domainId
     * @param string $searchedEntityName
     * @return \Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface
     */
    public function getSearchResultsProviderByDomainIdAndEntityName(
        int $domainId,
        string $searchedEntityName,
    ): SearchResultsProviderInterface {
        Assert::allIsInstanceOf($this->searchResultsProviders, $this->getSearchResultsProviderInterface());

        foreach ($this->getSearchResultsProvidersOrderedByPriority() as $searchResultsProvider) {
            if ($searchResultsProvider->isEnabledOnDomain($domainId)) {
                return $searchResultsProvider;
            }
        }

        throw new NoSearchResultsProviderEnabledOnDomainException($domainId, $searchedEntityName);
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Resolver\Search\SearchResultsProviderInterface[]
     */
    protected function getSearchResultsProvidersOrderedByPriority(): array
    {
        krsort($this->searchResultsProvidersServiceIdByPriority, SORT_NUMERIC);

        $searchResultsProvidersOrderedByPriority = [];

        foreach ($this->searchResultsProvidersServiceIdByPriority as $serviceId) {
            foreach ($this->searchResultsProviders as $searchResultsProvider) {
                if ($searchResultsProvider instanceof $serviceId) {
                    $searchResultsProvidersOrderedByPriority[] = $searchResultsProvider;
                }
            }
        }

        return $searchResultsProvidersOrderedByPriority;
    }

    /**
     * @param string $serviceId
     * @param int $priority
     */
    public function registerSearchResultsProvider(string $serviceId, int $priority): void
    {
        if (array_key_exists($priority, $this->searchResultsProvidersServiceIdByPriority)) {
            throw new SearchResultsProviderWithSamePriorityAlreadyExistsException($serviceId, $priority);
        }

        $this->searchResultsProvidersServiceIdByPriority[$priority] = $serviceId;
    }
}
