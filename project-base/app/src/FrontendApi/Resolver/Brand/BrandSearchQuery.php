<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Brand;

use App\Model\Product\Brand\BrandFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BrandSearchQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(private readonly BrandFacade $brandFacade)
    {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function brandSearchQuery(Argument $argument): array
    {
        $searchText = $argument['search'] ?? '';

        return $this->brandFacade->getBrandsForSearchText($searchText);
    }
}
