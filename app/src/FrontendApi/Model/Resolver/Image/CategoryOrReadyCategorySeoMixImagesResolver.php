<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class CategoryOrReadyCategorySeoMixImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @param string|null $type
     * @param array|null $sizes
     * @return array
     */
    public function resolveByCategoryOrReadyCategorySeoMix($categoryOrReadyCategorySeoMix, ?string $type, ?array $sizes): array
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        return $this->resolveByEntityId($categoryId, 'category', $type, $sizes);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByCategoryOrReadyCategorySeoMix' => 'categoryOrReadyCategorySeoMixImageResolver'];
    }
}
