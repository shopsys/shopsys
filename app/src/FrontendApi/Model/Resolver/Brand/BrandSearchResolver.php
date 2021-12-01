<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Brand;

use App\Model\Product\Brand\BrandFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class BrandSearchResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Product\Brand\BrandFacade
     */
    private BrandFacade $brandFacade;

    /**
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(BrandFacade $brandFacade)
    {
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function resolveSearch(Argument $argument): array
    {
        $searchText = $argument['search'] ?? '';

        return $this->brandFacade->getBrandsForSearchText($searchText);
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveSearch' => 'brandSearch',
        ];
    }
}
