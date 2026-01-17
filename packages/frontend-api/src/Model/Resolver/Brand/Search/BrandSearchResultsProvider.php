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
    public function __construct(
        protected readonly BrandFacade $brandFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getBrandSearchResults(
        Argument $argument,
    ): Promise|array {
        $searchText = $argument['searchInput']['search'] ?? '';

        return $this->brandFacade->getBrandsBySearchText($searchText);
    }

    #[Override]
    public function isEnabledOnDomain(int $domainId): bool
    {
        return true;
    }
}
