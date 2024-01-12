<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Search;

use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\Exception\NoProductSearchResultsProviderEnabledOnDomainException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\Exception\ProductSearchResultsProviderWithSamePriorityAlreadyExistsException;
use Webmozart\Assert\Assert;

class ProductSearchResultsProviderResolver
{
    /**
     * @var array<int, string>
     */
    protected array $productSearchResultsProvidersServiceIdByPriority = [];

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface[] $productSearchResultsProviders
     */
    public function __construct(
        protected readonly iterable $productSearchResultsProviders,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface
     */
    public function getProductsSearchResultsProviderByDomainId(
        int $domainId,
    ): ProductSearchResultsProviderInterface {
        Assert::allIsInstanceOf($this->productSearchResultsProviders, ProductSearchResultsProviderInterface::class);

        foreach ($this->getProductsSearchResultsProvidersOrderedByPriority() as $productSearchResultsProvider) {
            if ($productSearchResultsProvider->isEnabledOnDomain($domainId)) {
                return $productSearchResultsProvider;
            }
        }

        throw new NoProductSearchResultsProviderEnabledOnDomainException($domainId);
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Resolver\Products\Search\ProductSearchResultsProviderInterface[]
     */
    protected function getProductsSearchResultsProvidersOrderedByPriority(): array
    {
        krsort($this->productSearchResultsProvidersServiceIdByPriority, SORT_NUMERIC);

        $productSearchResultsProvidersOrderedByPriority = [];

        foreach ($this->productSearchResultsProvidersServiceIdByPriority as $serviceId) {
            foreach ($this->productSearchResultsProviders as $productSearchResultsProvider) {
                if ($productSearchResultsProvider instanceof $serviceId) {
                    $productSearchResultsProvidersOrderedByPriority[] = $productSearchResultsProvider;
                }
            }
        }

        return $productSearchResultsProvidersOrderedByPriority;
    }

    /**
     * @param string $serviceId
     * @param int $priority
     */
    public function registerProductSearchResultsProvider(string $serviceId, int $priority): void
    {
        if (array_key_exists($priority, $this->productSearchResultsProvidersServiceIdByPriority)) {
            throw new ProductSearchResultsProviderWithSamePriorityAlreadyExistsException($serviceId, $priority);
        }

        $this->productSearchResultsProvidersServiceIdByPriority[$priority] = $serviceId;
    }
}
