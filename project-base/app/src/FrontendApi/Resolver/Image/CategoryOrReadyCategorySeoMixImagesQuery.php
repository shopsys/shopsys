<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery;

class CategoryOrReadyCategorySeoMixImagesQuery extends ImagesQuery
{
    private const CATEGORY_ENTITY_NAME = 'category';

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByCategoryOrReadyCategorySeoMixPromiseQuery(
        $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class,
                ),
            );
        }

        return $this->resolveByEntityIdPromise($categoryId, self::CATEGORY_ENTITY_NAME, $type);
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByCategoryOrReadyCategorySeoMixPromiseQuery(
        $categoryOrReadyCategorySeoMix,
        ?string $type,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class,
                ),
            );
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
