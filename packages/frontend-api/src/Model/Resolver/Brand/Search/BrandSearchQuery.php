<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BrandSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function brandSearchQuery(Argument $argument): array
    {
        $searchText = $argument['search'] ?? '';

        return $this->brandFacade->getBrandsBySearchText($searchText);
    }
}
