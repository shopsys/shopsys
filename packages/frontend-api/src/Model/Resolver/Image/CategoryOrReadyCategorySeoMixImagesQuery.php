<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;

class CategoryOrReadyCategorySeoMixImagesQuery extends ImagesQuery
{
    protected const string CATEGORY_ENTITY_NAME = 'category';

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|\Shopsys\FrameworkBundle\Model\Category\Category $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByCategoryOrReadyCategorySeoMixPromiseQuery(
        ReadyCategorySeoMix|Category $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } else {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        }

        return $this->resolveByEntityIdPromise($categoryId, self::CATEGORY_ENTITY_NAME, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|\Shopsys\FrameworkBundle\Model\Category\Category $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByCategoryOrReadyCategorySeoMixPromiseQuery(
        ReadyCategorySeoMix|Category $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } else {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        }

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $categoryId,
                self::CATEGORY_ENTITY_NAME,
                $type,
            ),
        );
    }
}
