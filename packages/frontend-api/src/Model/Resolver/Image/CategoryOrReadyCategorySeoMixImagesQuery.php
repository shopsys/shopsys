<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade;

class CategoryOrReadyCategorySeoMixImagesQuery extends ImagesQuery
{
    protected const string CATEGORY_ENTITY_NAME = 'category';

    public function __construct(
        ImageConfig $imageConfig,
        DataLoaderInterface $imagesBatchLoader,
        DataLoaderInterface $firstImageBatchLoader,
        protected readonly ImageApiFacade $imageApiFacade,
    ) {
        parent::__construct($imageConfig, $imagesBatchLoader, $firstImageBatchLoader);
    }

    public function imagesByCategoryOrReadyCategorySeoMixPromiseQuery(
        ReadyCategorySeoMix|Category $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix && $this->hasReadyCategorySeoMixOwnImage($categoryOrReadyCategorySeoMix)) {
            return $this->imagesByEntityPromiseQuery($categoryOrReadyCategorySeoMix, $type);
        }

        return $this->resolveByEntityIdPromise(
            $this->getCategoryId($categoryOrReadyCategorySeoMix),
            self::CATEGORY_ENTITY_NAME,
            $type,
        );
    }

    public function mainImageByCategoryOrReadyCategorySeoMixPromiseQuery(
        ReadyCategorySeoMix|Category $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix && $this->hasReadyCategorySeoMixOwnImage($categoryOrReadyCategorySeoMix)) {
            return $this->mainImageByEntityPromiseQuery($categoryOrReadyCategorySeoMix, $type);
        }

        return $this->mainImageByEntityIdPromiseQuery(
            $this->getCategoryId($categoryOrReadyCategorySeoMix),
            self::CATEGORY_ENTITY_NAME,
            $type,
        );
    }

    protected function hasReadyCategorySeoMixOwnImage(ReadyCategorySeoMix $readyCategorySeoMix): bool
    {
        $readyCategorySeoMixEntityName = $this->imageConfig->getImageEntityConfig($readyCategorySeoMix)->getEntityName();
        $readyCategorySeoMixIdsWithImage = $this->imageApiFacade->getEntityIdsWithImageByEntityName($readyCategorySeoMixEntityName);

        return in_array($readyCategorySeoMix->getId(), $readyCategorySeoMixIdsWithImage, true);
    }

    protected function getCategoryId(ReadyCategorySeoMix|Category $categoryOrReadyCategorySeoMix): int
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            return $categoryOrReadyCategorySeoMix->getId();
        }

        return $categoryOrReadyCategorySeoMix->getCategory()->getId();
    }
}
