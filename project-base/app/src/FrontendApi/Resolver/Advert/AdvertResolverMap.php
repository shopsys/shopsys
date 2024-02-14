<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Advert;

use App\Model\Advert\Advert;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdvertResolverMap as BaseAdvertResolverMap;

class AdvertResolverMap extends BaseAdvertResolverMap
{
    protected const RESOLVER_CATEGORIES_FIELD = 'categories';

    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $categoriesBatchLoader
     */
    public function __construct(
        ImageFacade $imageFacade,
        private readonly DataLoaderInterface $categoriesBatchLoader,
    ) {
        parent::__construct($imageFacade);
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $commonAdvertResolverFields = [
            self::RESOLVER_CATEGORIES_FIELD => $this->mapVisibleCategories(...),
        ];

        $resultMap = parent::map();
        $resultMap[BaseAdvertResolverMap::RESOLVER_ADVERT_CODE] = $commonAdvertResolverFields;
        $resultMap[BaseAdvertResolverMap::RESOLVER_ADVERT_IMAGE] = $commonAdvertResolverFields;

        return $resultMap;
    }

    /**
     * @param \App\Model\Advert\Advert $advert
     * @return \GraphQL\Executor\Promise\Promise
     */
    private function mapVisibleCategories(Advert $advert): Promise
    {
        return $this->categoriesBatchLoader->load($advert->getCategoryIds());
    }
}
