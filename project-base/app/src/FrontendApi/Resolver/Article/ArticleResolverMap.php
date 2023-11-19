<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use DateTime;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class ArticleResolverMap extends ResolverMap
{
    /**
     * @return mixed[]
     */
    protected function map(): array
    {
        $map['ArticleSite'] = [
            'slug' => function (array $articleData) {
                return '/' . $articleData['mainSlug'];
            },
            'createdAt' => static function (array $blogArticleData): \DateTime {
                return new DateTime($blogArticleData['createdAt']);
            },
        ];

        return $map;
    }
}
