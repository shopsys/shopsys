<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandSearchResultsProvider implements BrandSearchResultsProviderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BrandFacade $brandFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|array
     */
    #[Override]
    public function getBrandSearchResults(
        Argument $argument,
    ): Promise|array {
        $searchText = $argument['searchInput']['search'] ?? '';

        return $this->brandFacade->getBrandsBySearchText($searchText);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    #[Override]
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
