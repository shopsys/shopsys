<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use Overblog\GraphQLBundle\Resolver\ResolverMap;

class ArticleResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        $map['Article'] = [
            'slug' => function (array $articleData) {
                return '/' . $articleData['mainSlug'];
            },
        ];

        return $map;
    }
}
