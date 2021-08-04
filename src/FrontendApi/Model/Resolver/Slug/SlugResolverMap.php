<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Slug;

use App\Model\Article\Article;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Store\Store;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class SlugResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Slug' => [
                self::RESOLVE_TYPE => function ($data) {
                    if ($data instanceof Article) {
                        return 'Article';
                    }

                    if ($data instanceof BlogCategory) {
                        return 'BlogCategory';
                    }

                    if ($data instanceof Brand) {
                        return 'Brand';
                    }

                    if ($data instanceof Category) {
                        return 'Category';
                    }

                    if ($data instanceof Store) {
                        return 'Store';
                    }

                    if (is_array($data)) {
                        if (array_key_exists('perex', $data) && array_key_exists('categories', $data)) {
                            return 'BlogArticle';
                        }

                        if ($data['is_main_variant']) {
                            return 'MainVariant';
                        }

                        if ($data['main_variant_id'] !== null) {
                            return 'Variant';
                        }

                        return 'RegularProduct';
                    }
                },
            ],
        ];
    }
}
