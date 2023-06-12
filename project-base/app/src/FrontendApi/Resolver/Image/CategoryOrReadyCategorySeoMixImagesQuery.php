<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class CategoryOrReadyCategorySeoMixImagesQuery extends ImagesQuery implements AliasedInterface
{
    private const CATEGORY_ENTITY_NAME = 'category';

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByCategoryOrReadyCategorySeoMixPromiseQuery(
        $categoryOrReadyCategorySeoMix,
        ?string $type,
        ?array $sizes,
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

        return $this->resolveByEntityIdPromise($categoryId, self::CATEGORY_ENTITY_NAME, $type, $sizes);
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByCategoryOrReadyCategorySeoMixPromiseQuery(
        $categoryOrReadyCategorySeoMix,
        ?string $type,
        ?string $size,
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

        $sizes = $size === null ? [] : [$size];
        $sizeConfigs = $this->getSizesConfigs($type, $sizes, self::CATEGORY_ENTITY_NAME);

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $categoryId,
                self::CATEGORY_ENTITY_NAME,
                $sizeConfigs,
                $type,
            ),
        );
    }
}
