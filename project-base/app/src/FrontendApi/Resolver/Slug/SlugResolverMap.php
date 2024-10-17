<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug;

use App\FrontendApi\Resolver\Slug\Exception\NoResultFoundForSlugUserError;
use App\Model\Article\Article;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Store\Store;

class SlugResolverMap extends ResolverMap
{
    public const SLUG_TYPE = 'slug_type';
    public const SLUG_TYPE_ARTICLE = 'article';
    public const SLUG_TYPE_BLOG_ARTICLE = 'blog_article';
    public const SLUG_TYPE_PRODUCT = 'product';

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Slug' => [
                self::RESOLVE_TYPE => function ($data) {
                    if ($data instanceof Article) {
                        return 'ArticleSite';
                    }

                    if ($data instanceof BlogCategory) {
                        return 'BlogCategory';
                    }

                    if ($data instanceof Brand) {
                        return 'Brand';
                    }

                    if ($data instanceof Category || $data instanceof ReadyCategorySeoMix) {
                        return 'Category';
                    }

                    if ($data instanceof Flag) {
                        return 'Flag';
                    }

                    if ($data instanceof Store) {
                        return 'Store';
                    }

                    if (is_array($data)) {
                        $typename = $this->resolveTypenameForEntitiesHydratedFromElasticsearch($data);

                        if ($typename !== null) {
                            return $typename;
                        }
                    }

                    throw new NoResultFoundForSlugUserError('Requested content does not exist.');
                },
            ],
        ];
    }

    /**
     * @param array $data
     * @return string|null
     */
    private function resolveTypenameForEntitiesHydratedFromElasticsearch(array $data): ?string
    {
        if (array_key_exists(self::SLUG_TYPE, $data)) {
            switch ($data[self::SLUG_TYPE]) {
                case self::SLUG_TYPE_ARTICLE:
                    return 'ArticleSite';
                case self::SLUG_TYPE_BLOG_ARTICLE:
                    return 'BlogArticle';
            }

            if ($data[self::SLUG_TYPE] === self::SLUG_TYPE_PRODUCT) {
                if ($data['is_main_variant']) {
                    return 'MainVariant';
                }

                if ($data['main_variant_id'] !== null) {
                    return 'Variant';
                }

                return 'RegularProduct';
            }
        }

        return null;
    }
}
